<?php

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
});

test('can view dashboard', function () {
  $this->actingAs($this->user)
    ->get('/')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Dashboard/Index')
    );
});

test('dashboard requires authentication', function () {
  $this->get('/')
    ->assertRedirect('/login');
});
