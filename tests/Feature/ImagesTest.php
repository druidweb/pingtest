<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('image route exists', function () {
  // Test that the image route is registered
  // Since Glide server handles the actual image processing,
  // we just test that the route exists and doesn't throw an error
  $response = $this->get('/img/test.jpg');

  // The route should exist (not 404 for route not found)
  // It might return 404 for file not found, but that's different
  expect($response->getStatusCode())->not->toBe(404);
});

test('image route handles parameters', function () {
  // Test that the image route accepts parameters
  $response = $this->get('/img/test.jpg?w=50&h=50&fit=crop');

  // The route should exist and handle parameters
  expect($response->getStatusCode())->not->toBe(404);
});
