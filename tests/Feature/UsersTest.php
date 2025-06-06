<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
  $this->account = Account::create(['name' => 'Acme Corporation']);
  $this->user = User::factory()->create([
    'account_id' => $this->account->id,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'johndoe@example.com',
    'owner' => true,
  ]);

  $this->regularUser = User::factory()->create([
    'account_id' => $this->account->id,
    'first_name' => 'Jane',
    'last_name' => 'Smith',
    'email' => 'jane@example.com',
    'owner' => false,
  ]);
});

test('can view users index', function () {
  $this->actingAs($this->user)
    ->get('/users')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Users/Index')
      ->has('users', 2)
      ->has('users.0', fn (Assert $assert) => $assert
        ->where('name', 'John Doe')
        ->where('email', 'johndoe@example.com')
        ->where('owner', true)
        ->has('id')
        ->has('photo')
        ->has('deleted_at')
      )
    );
});

test('can search users', function () {
  $this->actingAs($this->user)
    ->get('/users?search=Jane')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Users/Index')
      ->where('filters.search', 'Jane')
      ->has('users', 1)
      ->where('users.0.name', 'Jane Smith')
    );
});

test('can filter users by role', function () {
  $this->actingAs($this->user)
    ->get('/users?role=owner')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Users/Index')
      ->where('filters.role', 'owner')
      ->has('users', 1)
      ->where('users.0.owner', true)
    );
});

test('can view create user page', function () {
  $this->actingAs($this->user)
    ->get('/users/create')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Users/Create')
    );
});

test('can create user', function () {
  Storage::fake('local');

  $photo = UploadedFile::fake()->image('avatar.jpg');

  $this->actingAs($this->user)
    ->post('/users', [
      'first_name' => 'Bob',
      'last_name' => 'Johnson',
      'email' => 'bob@example.com',
      'password' => 'password123',
      'owner' => false,
      'photo' => $photo,
    ])
    ->assertRedirect('/users')
    ->assertSessionHas('success', 'User created.');

  $this->assertDatabaseHas('users', [
    'first_name' => 'Bob',
    'last_name' => 'Johnson',
    'email' => 'bob@example.com',
    'owner' => false,
    'account_id' => $this->account->id,
  ]);

  // Check that photo was stored
  $user = User::where('email', 'bob@example.com')->first();
  expect($user->photo_path)->not->toBeNull();
  Storage::assertExists($user->photo_path);
});

test('can create user without photo', function () {
  $this->actingAs($this->user)
    ->post('/users', [
      'first_name' => 'Bob',
      'last_name' => 'Johnson',
      'email' => 'bob@example.com',
      'password' => 'password123',
      'owner' => false,
    ])
    ->assertRedirect('/users')
    ->assertSessionHas('success', 'User created.');

  $this->assertDatabaseHas('users', [
    'first_name' => 'Bob',
    'last_name' => 'Johnson',
    'email' => 'bob@example.com',
    'photo_path' => null,
  ]);
});

test('validates required fields when creating user', function () {
  $this->actingAs($this->user)
    ->post('/users', [])
    ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'owner']);
});

test('validates unique email when creating user', function () {
  $this->actingAs($this->user)
    ->post('/users', [
      'first_name' => 'Bob',
      'last_name' => 'Johnson',
      'email' => 'johndoe@example.com', // Already exists
      'owner' => false,
    ])
    ->assertSessionHasErrors('email');
});

test('can view edit user page', function () {
  $this->actingAs($this->user)
    ->get("/users/{$this->regularUser->id}/edit")
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Users/Edit')
      ->has('user', fn (Assert $assert) => $assert
        ->where('id', $this->regularUser->id)
        ->where('first_name', 'Jane')
        ->where('last_name', 'Smith')
        ->where('email', 'jane@example.com')
        ->where('owner', false)
        ->has('photo')
        ->has('deleted_at')
      )
    );
});

test('can update user', function () {
  $this->actingAs($this->user)
    ->put("/users/{$this->regularUser->id}", [
      'first_name' => 'Janet',
      'last_name' => 'Smith',
      'email' => 'janet@example.com',
      'owner' => true,
    ])
    ->assertRedirect()
    ->assertSessionHas('success', 'User updated.');

  $this->assertDatabaseHas('users', [
    'id' => $this->regularUser->id,
    'first_name' => 'Janet',
    'last_name' => 'Smith',
    'email' => 'janet@example.com',
    'owner' => true,
  ]);
});

test('can update user with photo', function () {
  Storage::fake('local');
  $photo = UploadedFile::fake()->image('new-avatar.jpg');

  $this->actingAs($this->user)
    ->put("/users/{$this->regularUser->id}", [
      'first_name' => 'Janet',
      'last_name' => 'Smith',
      'email' => 'janet@example.com',
      'owner' => false,
      'photo' => $photo,
    ])
    ->assertRedirect()
    ->assertSessionHas('success', 'User updated.');

  $this->regularUser->refresh();
  expect($this->regularUser->photo_path)->not->toBeNull();
  Storage::assertExists($this->regularUser->photo_path);
});

test('can update user password', function () {
  $oldPassword = $this->regularUser->password;

  $this->actingAs($this->user)
    ->put("/users/{$this->regularUser->id}", [
      'first_name' => 'Jane',
      'last_name' => 'Smith',
      'email' => 'jane@example.com',
      'owner' => false,
      'password' => 'newpassword123',
    ])
    ->assertRedirect()
    ->assertSessionHas('success', 'User updated.');

  $this->regularUser->refresh();
  expect($this->regularUser->password)->not->toBe($oldPassword);
});

test('cannot update demo user', function () {
  // Use the existing user which already has the demo email
  $this->actingAs($this->user)
    ->put("/users/{$this->user->id}", [
      'first_name' => 'Updated',
      'last_name' => 'Name',
      'email' => 'updated@example.com',
      'owner' => false,
    ])
    ->assertRedirect()
    ->assertSessionHas('error', 'Updating the demo user is not allowed.');
});

test('can delete user', function () {
  $this->actingAs($this->user)
    ->delete("/users/{$this->regularUser->id}")
    ->assertRedirect()
    ->assertSessionHas('success', 'User deleted.');

  $this->assertSoftDeleted('users', ['id' => $this->regularUser->id]);
});

test('cannot delete demo user', function () {
  // Use the existing user which already has the demo email
  $this->actingAs($this->user)
    ->delete("/users/{$this->user->id}")
    ->assertRedirect()
    ->assertSessionHas('error', 'Deleting the demo user is not allowed.');

  $this->assertDatabaseHas('users', ['id' => $this->user->id, 'deleted_at' => null]);
});

test('can restore deleted user', function () {
  $this->regularUser->delete();

  $this->actingAs($this->user)
    ->put("/users/{$this->regularUser->id}/restore")
    ->assertRedirect()
    ->assertSessionHas('success', 'User restored.');

  $this->assertDatabaseHas('users', ['id' => $this->regularUser->id, 'deleted_at' => null]);
});

test('can filter deleted users', function () {
  $this->regularUser->delete();

  $this->actingAs($this->user)
    ->get('/users?trashed=with')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Users/Index')
      ->has('users', 2) // Both active and deleted users
    );

  $this->actingAs($this->user)
    ->get('/users?trashed=only')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Users/Index')
      ->has('users', 1) // Only deleted users
    );
});
