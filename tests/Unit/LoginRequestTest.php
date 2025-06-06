<?php

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
  $this->account = Account::create(['name' => 'Test Account']);
  $this->user = User::factory()->create([
    'account_id' => $this->account->id,
    'email' => 'test@example.com',
    'password' => 'password',
  ]);
});

test('authorize returns true', function () {
  $request = new LoginRequest;

  expect($request->authorize())->toBeTrue();
});

test('rules returns correct validation rules', function () {
  $request = new LoginRequest;
  $rules = $request->rules();

  expect($rules)->toHaveKey('email');
  expect($rules)->toHaveKey('password');
  expect($rules['email'])->toContain('required', 'string', 'email');
  expect($rules['password'])->toContain('required', 'string');
});

test('authenticate succeeds with valid credentials', function () {
  $request = LoginRequest::create('/login', 'POST', [
    'email' => 'test@example.com',
    'password' => 'password',
  ]);

  $request->authenticate();

  expect(Auth::check())->toBeTrue();
  expect(Auth::user()->email)->toBe('test@example.com');
});

test('authenticate fails with invalid credentials', function () {
  $request = LoginRequest::create('/login', 'POST', [
    'email' => 'test@example.com',
    'password' => 'wrong-password',
  ]);

  expect(fn () => $request->authenticate())
    ->toThrow(ValidationException::class);

  expect(Auth::check())->toBeFalse();
});

test('authenticate handles remember me', function () {
  $request = LoginRequest::create('/login', 'POST', [
    'email' => 'test@example.com',
    'password' => 'password',
    'remember' => true,
  ]);

  $request->authenticate();

  expect(Auth::check())->toBeTrue();
});

test('throttle key is generated correctly', function () {
  $request = LoginRequest::create('/login', 'POST', [
    'email' => 'Test@Example.com',
  ]);
  $request->setUserResolver(fn () => null);

  $throttleKey = $request->throttleKey();

  expect($throttleKey)->toContain('test@example.com');
  expect($throttleKey)->toContain($request->ip());
});

test('ensure is not rate limited passes when under limit', function () {
  $request = LoginRequest::create('/login', 'POST', [
    'email' => 'test@example.com',
    'password' => 'password',
  ]);

  // Should not throw an exception
  $request->ensureIsNotRateLimited();

  expect(true)->toBeTrue(); // If we get here, no exception was thrown
});

test('ensure is not rate limited throws exception when over limit', function () {
  $request = LoginRequest::create('/login', 'POST', [
    'email' => 'test@example.com',
    'password' => 'password',
  ]);

  // Simulate hitting the rate limit
  $throttleKey = $request->throttleKey();
  for ($i = 0; $i < 6; $i++) {
    RateLimiter::hit($throttleKey);
  }

  expect(fn () => $request->ensureIsNotRateLimited())
    ->toThrow(ValidationException::class);
});

test('authenticate clears rate limiter on successful login', function () {
  $request = LoginRequest::create('/login', 'POST', [
    'email' => 'test@example.com',
    'password' => 'password',
  ]);

  // Hit the rate limiter a few times
  $throttleKey = $request->throttleKey();
  RateLimiter::hit($throttleKey);
  RateLimiter::hit($throttleKey);

  expect(RateLimiter::attempts($throttleKey))->toBe(2);

  $request->authenticate();

  expect(RateLimiter::attempts($throttleKey))->toBe(0);
});

test('authenticate increments rate limiter on failed login', function () {
  $request = LoginRequest::create('/login', 'POST', [
    'email' => 'test@example.com',
    'password' => 'wrong-password',
  ]);

  $throttleKey = $request->throttleKey();
  $initialAttempts = RateLimiter::attempts($throttleKey);

  try {
    $request->authenticate();
  } catch (ValidationException $e) {
    // Expected
  }

  expect(RateLimiter::attempts($throttleKey))->toBe($initialAttempts + 1);
});
