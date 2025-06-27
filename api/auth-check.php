<?php

global $auth0_domain, $auth0_audience, $auth0_client_id;
require __DIR__ . '/vendor/autoload.php'; // Adjust path as needed

use Auth0\SDK\Auth0;
use Auth0\SDK\Configuration\SdkConfiguration;
use Auth0\SDK\Exception\ConfigurationException;
use Auth0\SDK\Exception\InvalidTokenException;


// Your Auth0 Domain (e.g., 'dev-dsqiozlefe57u37d.us.auth0.com')
define('AUTH0_DOMAIN', $auth0_domain);
// The EXACT Identifier of your API in Auth0 Dashboard (e.g., 'http://localhost:8081')
define('AUTH0_AUDIENCE', $auth0_audience); // Make sure this matches your Auth0 API Identifier


// 1. Get the Authorization header from the request
$authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if (!$authorizationHeader || !str_starts_with($authorizationHeader, 'Bearer ')) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'message' => 'Authorization header missing or invalid.']);
    exit;
}

$token = substr($authorizationHeader, 7); // Extract the JWT token (remove 'Bearer ')

try {
    $config = new SdkConfiguration(
        strategy: SdkConfiguration::STRATEGY_API,
        domain: 'https://' . $auth0_domain,
        clientId: $auth0_client_id,
        audience: [$auth0_audience],

    );

    $auth0 = new Auth0($config);
    $token = $auth0->decode($token);

} catch (ConfigurationException $e) {
    //http_response_code(500);
    echo 'Auth0 SDK configuration error: ' . $e->getMessage();
    exit;
} catch (InvalidTokenException $e) {
    //http_response_code(401);
    echo 'Invalid token: ' . $e->getMessage();
    exit;
} catch (Throwable $e) { // Catch any other unexpected errors
    //http_response_code(500);
    echo 'An unexpected error occurred: ' . $e->getMessage();
    exit;
}