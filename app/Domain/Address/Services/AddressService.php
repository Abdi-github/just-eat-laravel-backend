<?php

declare(strict_types=1);

namespace App\Domain\Address\Services;

use App\Domain\Address\Models\Address;
use App\Domain\Address\Repositories\AddressRepositoryInterface;
use Illuminate\Support\Collection;

class AddressService
{
    public function __construct(private readonly AddressRepositoryInterface $addresses) {}

    public function getForUser(int $userId): Collection
    {
        return $this->addresses->findByUser($userId);
    }

    public function findForUser(int $id, int $userId): ?Address
    {
        return $this->addresses->findByIdForUser($id, $userId);
    }

    public function create(int $userId, array $data): Address
    {
        if (! empty($data['is_default'])) {
            $this->addresses->clearDefaultForUser($userId);
        } elseif (! $this->addresses->userHasAddresses($userId)) {
            $data['is_default'] = true;
        }

        $data['user_id'] = $userId;

        return $this->addresses->create($data);
    }

    public function update(int $id, int $userId, array $data): ?Address
    {
        $address = $this->addresses->findByIdForUser($id, $userId);

        if (! $address) {
            return null;
        }

        if (! empty($data['is_default'])) {
            $this->addresses->clearDefaultForUser($userId);
        }

        return $this->addresses->update($id, $data);
    }

    public function delete(int $id, int $userId): bool
    {
        $address = $this->addresses->findByIdForUser($id, $userId);

        if (! $address) {
            return false;
        }

        return $this->addresses->delete($id);
    }

    public function setDefault(int $id, int $userId): ?Address
    {
        $address = $this->addresses->findByIdForUser($id, $userId);

        if (! $address) {
            return null;
        }

        $this->addresses->clearDefaultForUser($userId);

        return $this->addresses->update($id, ['is_default' => true]);
    }
}
