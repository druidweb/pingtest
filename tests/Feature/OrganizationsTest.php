<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
  $this->user = User::factory()->create([
    'account_id' => Account::create(['name' => 'Acme Corporation'])->id,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'johndoe@example.com',
    'owner' => true,
  ]);

  $this->user->account->organizations()->createMany([
    [
      'name' => 'Apple',
      'email' => 'info@apple.com',
      'phone' => '647-943-4400',
      'address' => '1600-120 Bremner Blvd',
      'city' => 'Toronto',
      'region' => 'ON',
      'country' => 'CA',
      'postal_code' => 'M5J 0A8',
    ], [
      'name' => 'Microsoft',
      'email' => 'info@microsoft.com',
      'phone' => '877-568-2495',
      'address' => 'One Microsoft Way',
      'city' => 'Redmond',
      'region' => 'WA',
      'country' => 'US',
      'postal_code' => '98052',
    ],
  ]);
});

test('can view organizations', function () {
  $this->actingAs($this->user)
    ->get('/organizations')
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Organizations/Index')
      ->has('organizations.data', 2)
      ->has('organizations.data.0', fn (Assert $assert) => $assert
        ->has('id')
        ->where('name', 'Apple')
        ->where('phone', '647-943-4400')
        ->where('city', 'Toronto')
        ->where('deleted_at', null)
      )
      ->has('organizations.data.1', fn (Assert $assert) => $assert
        ->has('id')
        ->where('name', 'Microsoft')
        ->where('phone', '877-568-2495')
        ->where('city', 'Redmond')
        ->where('deleted_at', null)
      )
    );
});

test('can search for organizations', function () {
  $this->actingAs($this->user)
    ->get('/organizations?search=Apple')
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Organizations/Index')
      ->where('filters.search', 'Apple')
      ->has('organizations.data', 1)
      ->has('organizations.data.0', fn (Assert $assert) => $assert
        ->has('id')
        ->where('name', 'Apple')
        ->where('phone', '647-943-4400')
        ->where('city', 'Toronto')
        ->where('deleted_at', null)
      )
    );
});

test('cannot view deleted organizations', function () {
  $this->user->account->organizations()->firstWhere('name', 'Microsoft')->delete();

  $this->actingAs($this->user)
    ->get('/organizations')
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Organizations/Index')
      ->has('organizations.data', 1)
      ->where('organizations.data.0.name', 'Apple')
    );
});

test('can filter to view deleted organizations', function () {
  $this->user->account->organizations()->firstWhere('name', 'Microsoft')->delete();

  $this->actingAs($this->user)
    ->get('/organizations?trashed=with')
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Organizations/Index')
      ->has('organizations.data', 2)
      ->where('organizations.data.0.name', 'Apple')
      ->where('organizations.data.1.name', 'Microsoft')
    );
});

test('can view create organization page', function () {
  $this->actingAs($this->user)
    ->get('/organizations/create')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Organizations/Create')
    );
});

test('can create organization', function () {
  $this->actingAs($this->user)
    ->post('/organizations', [
      'name' => 'Google',
      'email' => 'info@google.com',
      'phone' => '555-123-4567',
      'address' => '1600 Amphitheatre Parkway',
      'city' => 'Mountain View',
      'region' => 'CA',
      'country' => 'US',
      'postal_code' => '94043',
    ])
    ->assertRedirect('/organizations')
    ->assertSessionHas('success', 'Organization created.');

  $this->assertDatabaseHas('organizations', [
    'name' => 'Google',
    'email' => 'info@google.com',
    'phone' => '555-123-4567',
    'account_id' => $this->user->account_id,
  ]);
});

test('validates required fields when creating organization', function () {
  $this->actingAs($this->user)
    ->post('/organizations', [])
    ->assertSessionHasErrors(['name']);
});

test('can view edit organization page', function () {
  $organization = $this->user->account->organizations()->first();

  $this->actingAs($this->user)
    ->get("/organizations/{$organization->id}/edit")
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Organizations/Edit')
      ->has('organization', fn (Assert $assert) => $assert
        ->where('id', $organization->id)
        ->where('name', 'Apple')
        ->where('email', 'info@apple.com')
        ->where('phone', '647-943-4400')
        ->has('address')
        ->has('city')
        ->has('region')
        ->has('country')
        ->has('postal_code')
        ->has('deleted_at')
        ->has('contacts')
      )
    );
});

test('can update organization', function () {
  $organization = $this->user->account->organizations()->first();

  $this->actingAs($this->user)
    ->put("/organizations/{$organization->id}", [
      'name' => 'Apple Inc.',
      'email' => 'contact@apple.com',
      'phone' => '647-943-4401',
      'address' => 'Updated Address',
      'city' => 'Cupertino',
      'region' => 'CA',
      'country' => 'US',
      'postal_code' => '95014',
    ])
    ->assertRedirect()
    ->assertSessionHas('success', 'Organization updated.');

  $this->assertDatabaseHas('organizations', [
    'id' => $organization->id,
    'name' => 'Apple Inc.',
    'email' => 'contact@apple.com',
    'phone' => '647-943-4401',
  ]);
});

test('can delete organization', function () {
  $organization = $this->user->account->organizations()->first();

  $this->actingAs($this->user)
    ->delete("/organizations/{$organization->id}")
    ->assertRedirect()
    ->assertSessionHas('success', 'Organization deleted.');

  $this->assertSoftDeleted('organizations', ['id' => $organization->id]);
});

test('can restore deleted organization', function () {
  $organization = $this->user->account->organizations()->first();
  $organization->delete();

  $this->actingAs($this->user)
    ->put("/organizations/{$organization->id}/restore")
    ->assertRedirect()
    ->assertSessionHas('success', 'Organization restored.');

  $this->assertDatabaseHas('organizations', ['id' => $organization->id, 'deleted_at' => null]);
});
