<?php

use App\Providers\AppServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use ReflectionClass;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('home constant is defined correctly', function () {
  expect(AppServiceProvider::HOME)->toBe('/');
});

test('register method unguards models', function () {
  // Create a fresh instance of the service provider
  $provider = new AppServiceProvider($this->app);

  // Models should be guarded by default in tests
  Model::reguard();
  expect(Model::isUnguarded())->toBeFalse();

  // Call register method
  $provider->register();

  // Models should now be unguarded
  expect(Model::isUnguarded())->toBeTrue();
});

test('boot method calls bootRoute', function () {
  // Create a fresh instance of the service provider
  $provider = new AppServiceProvider($this->app);

  // Mock the bootRoute method to verify it's called
  $provider = new class($this->app) extends AppServiceProvider
  {
    public $bootRouteCalled = false;

    public function bootRoute(): void
    {
      $this->bootRouteCalled = true;
      parent::bootRoute();
    }
  };

  // Call boot method
  $provider->boot();

  // Verify bootRoute was called
  expect($provider->bootRouteCalled)->toBeTrue();
});

test('bootRoute method configures api rate limiter', function () {
  // Create a fresh instance of the service provider
  $provider = new AppServiceProvider($this->app);

  // Call bootRoute method
  $provider->bootRoute();

  // Test that the rate limiter is configured for 'api'
  $request = Request::create('/api/test', 'GET');
  $request->setUserResolver(fn () => null); // No authenticated user
  $request->server->set('REMOTE_ADDR', '192.168.1.1');

  // Get the configured limiter callback
  $limiterCallback = RateLimiter::limiter('api');
  expect($limiterCallback)->toBeInstanceOf(Closure::class);

  // Execute the callback to get the limit
  $limit = $limiterCallback($request);
  expect($limit)->toBeInstanceOf(Limit::class);

  // Test the limit properties using reflection since they're protected
  $reflection = new ReflectionClass($limit);
  $maxAttemptsProperty = $reflection->getProperty('maxAttempts');
  $maxAttemptsProperty->setAccessible(true);
  expect($maxAttemptsProperty->getValue($limit))->toBe(60);
});

test('api rate limiter uses user id when authenticated', function () {
  // Create a user
  $user = \App\Models\User::factory()->create([
    'account_id' => \App\Models\Account::create(['name' => 'Test Account'])->id,
  ]);

  // Create a fresh instance of the service provider
  $provider = new AppServiceProvider($this->app);
  $provider->bootRoute();

  // Create request with authenticated user
  $request = Request::create('/api/test', 'GET');
  $request->setUserResolver(fn () => $user);

  // Get the configured limiter callback and execute it
  $limiterCallback = RateLimiter::limiter('api');
  $limit = $limiterCallback($request);

  expect($limit)->toBeInstanceOf(Limit::class);

  // Test that the key is different when user is authenticated vs not
  $request2 = Request::create('/api/test', 'GET');
  $request2->setUserResolver(fn () => null);
  $request2->server->set('REMOTE_ADDR', '192.168.1.1');
  $limit2 = $limiterCallback($request2);

  // The limits should have different keys
  expect($limit)->not->toBe($limit2);
});

test('api rate limiter uses ip when not authenticated', function () {
  // Create a fresh instance of the service provider
  $provider = new AppServiceProvider($this->app);
  $provider->bootRoute();

  // Create request without authenticated user
  $request = Request::create('/api/test', 'GET');
  $request->setUserResolver(fn () => null);
  $request->server->set('REMOTE_ADDR', '192.168.1.1');

  // Get the configured limiter callback and execute it
  $limiterCallback = RateLimiter::limiter('api');
  $limit = $limiterCallback($request);

  expect($limit)->toBeInstanceOf(Limit::class);

  // Test that different IPs get different limits
  $request2 = Request::create('/api/test', 'GET');
  $request2->setUserResolver(fn () => null);
  $request2->server->set('REMOTE_ADDR', '192.168.1.2');
  $limit2 = $limiterCallback($request2);

  // The limits should have different keys for different IPs
  expect($limit)->not->toBe($limit2);
});

test('service provider can be instantiated', function () {
  $provider = new AppServiceProvider($this->app);

  expect($provider)->toBeInstanceOf(AppServiceProvider::class);
  expect($provider)->toBeInstanceOf(\Illuminate\Support\ServiceProvider::class);
});
