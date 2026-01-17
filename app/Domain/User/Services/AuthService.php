<?php

namespace App\Domain\User\Services;

use App\Domain\Address\Repositories\AddressRepositoryInterface;
use App\Domain\Restaurant\Repositories\RestaurantRepositoryInterface;
use App\Domain\User\Models\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RestaurantRepositoryInterface $restaurants,
        private readonly AddressRepositoryInterface $addresses,
    ) {}

    public function register(array $data): array
    {
        $user = $this->users->create([
            'username'           => $data['username'],
            'email'              => $data['email'],
            'password'           => $data['password'],
            'first_name'         => $data['first_name'] ?? null,
            'last_name'          => $data['last_name'] ?? null,
            'phone'              => $data['phone'] ?? null,
            'preferred_language' => $data['preferred_language'] ?? 'fr',
        ]);

        $user->assignRole('customer');

        $token = $user->createToken('api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function login(array $data): array
    {
        $user = $this->users->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if (! $user->is_active) {
            throw new \RuntimeException('Account is inactive.');
        }

        $token = $user->createToken('api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function registerRestaurant(array $data): array
    {
        $user = $this->users->create([
            'username'           => $data['username'],
            'email'              => $data['email'],
            'password'           => $data['password'],
            'first_name'         => $data['first_name'] ?? null,
            'last_name'          => $data['last_name'] ?? null,
            'phone'              => $data['phone'] ?? null,
            'preferred_language' => $data['preferred_language'] ?? 'fr',
        ]);

        $user->assignRole('restaurant_owner');

        $slug = Str::slug($data['restaurant_name']) . '-' . Str::random(6);

        $restaurantData = [
            'name'                    => $data['restaurant_name'],
            'slug'                    => $slug,
            'user_id'                 => $user->id,
            'phone'                   => $data['restaurant_phone'] ?? null,
            'email'                   => $data['restaurant_email'] ?? null,
            'is_active'               => false,
            'is_featured'             => false,
            'price_range'             => 'moderate',
            'minimum_order'           => 0,
            'delivery_fee'            => 0,
            'estimated_delivery_time' => 30,
            'accepts_pickup'          => true,
            'accepts_delivery'        => true,
            'average_rating'          => 0,
            'total_reviews'           => 0,
        ];

        if (! empty($data['city_id']) && ! empty($data['canton_id'])) {
            $address = $this->addresses->create([
                'street'    => $data['street'] ?? 'TBD',
                'zip_code'  => $data['zip_code'] ?? '0000',
                'city_id'   => $data['city_id'],
                'canton_id' => $data['canton_id'],
            ]);
            $restaurantData['address_id'] = $address->id;
        }

        $this->restaurants->create($restaurantData);

        $token = $user->createToken('api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function registerCourier(array $data): array
    {
        $user = $this->users->create([
            'username'           => $data['username'],
            'email'              => $data['email'],
            'password'           => $data['password'],
            'first_name'         => $data['first_name'] ?? null,
            'last_name'          => $data['last_name'] ?? null,
            'phone'              => $data['phone'] ?? null,
            'preferred_language' => $data['preferred_language'] ?? 'fr',
        ]);

        $user->assignRole('delivery_driver');

        $token = $user->createToken('api')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function forgotPassword(string $email): string
    {
        return Password::broker('users')->sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): string
    {
        return Password::broker('users')->reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete();
            }
        );
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new \RuntimeException('Current password is incorrect.');
        }

        $this->users->update($user->id, ['password' => $newPassword]);
    }

    public function verifyEmail(int $id, string $hash): User
    {
        $user = $this->users->findById($id);

        if (! $user) {
            throw new \RuntimeException('User not found.');
        }

        if (! hash_equals($hash, sha1($user->email))) {
            throw new \RuntimeException('Invalid verification link.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return $user;
    }

    public function resendVerification(string $email): void
    {
        $user = $this->users->findByEmail($email);

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }
    }
}
