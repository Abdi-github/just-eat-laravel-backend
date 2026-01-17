<?php

use App\Domain\Address\Models\Address;
use App\Domain\Location\Models\Canton;
use App\Domain\Location\Models\City;
use Illuminate\Support\Str;

// ─── Helpers ──────────────────────────────────────────────────────────────────

function makeAddressCity(): array
{
    $canton = Canton::create([
        'code'   => strtoupper(Str::random(2)),
        'name'   => json_encode(['fr' => 'Vaud', 'de' => 'Waadt']),
        'region' => 'Romandy',
    ]);

    $city = City::create([
        'name'      => 'Lausanne',
        'canton_id' => $canton->id,
        'zip_code'  => '1000',
    ]);

    return ['canton' => $canton, 'city' => $city];
}

// ─── Tests ────────────────────────────────────────────────────────────────────

it('requires authentication to access addresses', function () {
    $this->getJson('/api/v1/addresses')->assertUnauthorized();
});

it('returns empty address list for a new user', function () {
    $user = createUser();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/addresses')
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(0, 'data');
});

it('creates an address for the authenticated user', function () {
    $user  = createUser();
    $place = makeAddressCity();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/addresses', [
            'street'    => 'Rue du Midi',
            'zip_code'  => '1000',
            'city_id'   => $place['city']->id,
            'canton_id' => $place['canton']->id,
        ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.street', 'Rue du Midi')
        ->assertJsonPath('data.is_default', true); // first address auto-defaults
});

it('first address is automatically the default', function () {
    $user  = createUser();
    $place = makeAddressCity();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/addresses', [
            'street'    => 'Avenue de la Gare',
            'zip_code'  => '1000',
            'city_id'   => $place['city']->id,
            'canton_id' => $place['canton']->id,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.is_default', true);
});

it('shows a specific user address', function () {
    $user  = createUser();
    $place = makeAddressCity();

    $address = Address::create([
        'user_id'   => $user->id,
        'street'    => 'Chemin des Fleurs',
        'zip_code'  => '1000',
        'city_id'   => $place['city']->id,
        'canton_id' => $place['canton']->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson("/api/v1/addresses/{$address->id}")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.street', 'Chemin des Fleurs');
});

it('returns 404 when accessing another users address', function () {
    $owner = createUser();
    $other = createUser();
    $place = makeAddressCity();

    $address = Address::create([
        'user_id'   => $owner->id,
        'street'    => 'Private Street',
        'zip_code'  => '1000',
        'city_id'   => $place['city']->id,
        'canton_id' => $place['canton']->id,
    ]);

    $this->actingAs($other, 'sanctum')
        ->getJson("/api/v1/addresses/{$address->id}")
        ->assertNotFound()
        ->assertJsonPath('success', false);
});

it('updates an address', function () {
    $user  = createUser();
    $place = makeAddressCity();

    $address = Address::create([
        'user_id'   => $user->id,
        'street'    => 'Old Street',
        'zip_code'  => '1000',
        'city_id'   => $place['city']->id,
        'canton_id' => $place['canton']->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->putJson("/api/v1/addresses/{$address->id}", ['street' => 'New Street'])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.street', 'New Street');
});

it('deletes an address', function () {
    $user  = createUser();
    $place = makeAddressCity();

    $address = Address::create([
        'user_id'   => $user->id,
        'street'    => 'Doomed Street',
        'zip_code'  => '1000',
        'city_id'   => $place['city']->id,
        'canton_id' => $place['canton']->id,
    ]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/v1/addresses/{$address->id}")
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(Address::find($address->id))->toBeNull();
});

it('sets an address as default', function () {
    $user  = createUser();
    $place = makeAddressCity();

    $addr1 = Address::create([
        'user_id'    => $user->id,
        'street'     => 'First Street',
        'zip_code'   => '1000',
        'city_id'    => $place['city']->id,
        'canton_id'  => $place['canton']->id,
        'is_default' => true,
    ]);

    $addr2 = Address::create([
        'user_id'    => $user->id,
        'street'     => 'Second Street',
        'zip_code'   => '1000',
        'city_id'    => $place['city']->id,
        'canton_id'  => $place['canton']->id,
        'is_default' => false,
    ]);

    $this->actingAs($user, 'sanctum')
        ->patchJson("/api/v1/addresses/{$addr2->id}/default")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_default', true);

    expect(Address::find($addr1->id)->is_default)->toBeFalse();
    expect(Address::find($addr2->id)->is_default)->toBeTrue();
});

it('cannot modify another users address', function () {
    $owner = createUser();
    $other = createUser();
    $place = makeAddressCity();

    $address = Address::create([
        'user_id'   => $owner->id,
        'street'    => 'Owner Street',
        'zip_code'  => '1000',
        'city_id'   => $place['city']->id,
        'canton_id' => $place['canton']->id,
    ]);

    $this->actingAs($other, 'sanctum')
        ->putJson("/api/v1/addresses/{$address->id}", ['street' => 'Hacked'])
        ->assertNotFound();
});
