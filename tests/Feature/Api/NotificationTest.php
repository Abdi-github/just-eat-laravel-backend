<?php

use App\Domain\Notification\Models\Notification;

// ─── Notification API Tests ────────────────────────────────────────────────────

function makeNotification(int $userId, array $overrides = []): Notification
{
    return Notification::create(array_merge([
        'user_id'  => $userId,
        'type'     => 'SYSTEM',
        'title'    => 'Test Notification',
        'body'     => 'This is a test notification body.',
        'channel'  => 'BOTH',
        'priority' => 'NORMAL',
        'is_read'  => false,
    ], $overrides));
}

it('requires auth to list notifications', function () {
    $this->getJson('/api/v1/notifications')->assertUnauthorized();
});

it('returns empty notification list for a new user', function () {
    $user = createUser();

    $this->actingAs($user)->getJson('/api/v1/notifications')
         ->assertOk()
         ->assertJsonPath('success', true)
         ->assertJsonPath('data', []);
});

it('lists user notifications', function () {
    $user = createUser();
    makeNotification($user->id);
    makeNotification($user->id, ['title' => 'Second']);

    $r = $this->actingAs($user)->getJson('/api/v1/notifications');

    $r->assertOk()
      ->assertJsonPath('success', true)
      ->assertJsonPath('pagination.total', 2);
});

it('does not return other users notifications', function () {
    $user1 = createUser();
    $user2 = createUser();

    makeNotification($user1->id);
    makeNotification($user2->id);

    $r = $this->actingAs($user1)->getJson('/api/v1/notifications');

    $r->assertOk()->assertJsonPath('pagination.total', 1);
});

it('returns notification count', function () {
    $user = createUser();
    makeNotification($user->id);
    makeNotification($user->id, ['is_read' => true]);

    $r = $this->actingAs($user)->getJson('/api/v1/notifications/count');

    $r->assertOk()
      ->assertJsonPath('data.total', 2)
      ->assertJsonPath('data.unread', 1);
});

it('marks a notification as read', function () {
    $user         = createUser();
    $notification = makeNotification($user->id);

    expect($notification->is_read)->toBeFalse();

    $r = $this->actingAs($user)->patchJson("/api/v1/notifications/{$notification->id}/read");

    $r->assertOk()
      ->assertJsonPath('data.is_read', true);
});

it('returns 404 when marking another users notification as read', function () {
    $user1 = createUser();
    $user2 = createUser();
    $notification = makeNotification($user1->id);

    $this->actingAs($user2)->patchJson("/api/v1/notifications/{$notification->id}/read")
         ->assertNotFound();
});

it('marks all notifications as read', function () {
    $user = createUser();
    makeNotification($user->id);
    makeNotification($user->id);
    makeNotification($user->id);

    $this->actingAs($user)->patchJson('/api/v1/notifications/read-all')
         ->assertOk()
         ->assertJsonPath('data.updated', 3);

    expect(Notification::where('user_id', $user->id)->where('is_read', false)->count())->toBe(0);
});

it('deletes a specific notification', function () {
    $user         = createUser();
    $notification = makeNotification($user->id);

    $this->actingAs($user)->deleteJson("/api/v1/notifications/{$notification->id}")
         ->assertOk();

    expect(Notification::find($notification->id))->toBeNull();
});

it('returns 404 when deleting another users notification', function () {
    $user1 = createUser();
    $user2 = createUser();
    $notification = makeNotification($user1->id);

    $this->actingAs($user2)->deleteJson("/api/v1/notifications/{$notification->id}")
         ->assertNotFound();
});

it('deletes all notifications for a user', function () {
    $user = createUser();
    makeNotification($user->id);
    makeNotification($user->id);

    $this->actingAs($user)->deleteJson('/api/v1/notifications')
         ->assertOk()
         ->assertJsonPath('data.deleted', 2);

    expect(Notification::where('user_id', $user->id)->count())->toBe(0);
});

it('filters notifications by is_read', function () {
    $user = createUser();
    makeNotification($user->id, ['is_read' => true]);
    makeNotification($user->id, ['is_read' => false]);

    $r = $this->actingAs($user)->getJson('/api/v1/notifications?is_read=true');

    $r->assertOk()->assertJsonPath('pagination.total', 1);
});
