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
    'password' => 'password',
    'owner' => true,
  ]);
});

test('can view login page', function () {
  $this->get('/login')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Auth/Login')
    );
});

test('can login with valid credentials', function () {
  $this->post('/login', [
    'email' => 'johndoe@example.com',
    'password' => 'password',
  ])
    ->assertRedirect('/')
    ->assertSessionHasNoErrors();

  $this->assertAuthenticatedAs($this->user);
});

test('cannot login with invalid credentials', function () {
  $this->post('/login', [
    'email' => 'johndoe@example.com',
    'password' => 'wrong-password',
  ])
    ->assertSessionHasErrors('email');

  $this->assertGuest();
});

test('requires email and password', function () {
  $this->post('/login', [])
    ->assertSessionHasErrors(['email', 'password']);
});

test('requires valid email format', function () {
  $this->post('/login', [
    'email' => 'invalid-email',
    'password' => 'password',
  ])
    ->assertSessionHasErrors('email');
});

test('can logout', function () {
  $this->actingAs($this->user)
    ->delete('/logout')
    ->assertRedirect('/');

  $this->assertGuest();
});

test('guest cannot access protected routes', function () {
  $this->get('/')
    ->assertRedirect('/login');
});

test('authenticated user can access dashboard', function () {
  $this->actingAs($this->user)
    ->get('/')
    ->assertStatus(200)
    ->assertInertia(fn (Assert $assert) => $assert
      ->component('Dashboard/Index')
    );
});
