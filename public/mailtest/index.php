<?php
session_start(); // Start the session

require 'config.php';

use League\OAuth2\Client\Provider\GenericProvider;

// Check if access token exists in session
if (isset($_SESSION['accessToken'])) {
    // Redirect to a page where authenticated actions occur
    header('Location: dashboard.php'); // or another page
    exit();
}

// Create the OAuth2 provider instance
$oauthClient = new GenericProvider([
    'clientId'                => $config['appId'],
    'clientSecret'            => $config['appSecret'],
    'redirectUri'             => $config['redirectUri'],
    'urlAuthorize'            => "https://login.microsoftonline.com/{$config['tenantId']}/oauth2/v2.0/authorize",
    'urlAccessToken'          => "https://login.microsoftonline.com/{$config['tenantId']}/oauth2/v2.0/token",
    'urlResourceOwnerDetails' => '',
    'scopes'                  => explode(' ', $config['scopes'])
]);

// Generate authorization URL and redirect
$authorizationUrl = $oauthClient->getAuthorizationUrl();
$_SESSION['oauth2state'] = $oauthClient->getState(); // Store state in session

header('Location: ' . $authorizationUrl);
exit();
?>
