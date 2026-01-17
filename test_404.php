<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$request = Illuminate\Http\Request::create('/api/v1/brands/99999', 'GET');
$request->headers->set('Accept', 'application/json');
$response = $app->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Body: " . $response->getContent() . "\n";
