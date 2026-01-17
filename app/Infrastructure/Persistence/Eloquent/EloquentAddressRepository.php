<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Address\Models\Address;
use App\Domain\Address\Repositories\AddressRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentAddressRepository implements AddressRepositoryInterface
{
    public function __construct(private Address $model) {}

    public function findById(int $id): ?Address
    {
        return $this->model->find($id);
    }

    public function findByIdForUser(int $id, int $userId): ?Address
    {
        return $this->model->where('user_id', $userId)->with(['city', 'canton'])->find($id);
    }

    public function findByUser(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with(['city', 'canton'])
            ->orderByDesc('is_default')
            ->get();
    }

    public function clearDefaultForUser(int $userId): void
    {
        $this->model->where('user_id', $userId)->update(['is_default' => false]);
    }

    public function userHasAddresses(int $userId): bool
    {
        return $this->model->where('user_id', $userId)->exists();
    }

    public function create(array $data): Address
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Address
    {
        $address = $this->model->findOrFail($id);
        $address->update($data);
        return $address->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
