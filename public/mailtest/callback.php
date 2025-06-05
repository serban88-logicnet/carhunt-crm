<?php
session_start(); // Start the session

require 'config.php';

use League\OAuth2\Client\Provider\GenericProvider;

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

// Check for errors
if (isset($_GET['error'])) {
    exit('Error: ' . htmlspecialchars($_GET['error']));
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
    unset($_SESSION['oauth2state']); // Clean up invalid state
    exit('Invalid state');
}

// Exchange the authorization code for an access token
try {
    $accessToken = $oauthClient->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    // Store the access token details in the session
    $_SESSION['accessToken'] = $accessToken->jsonSerialize(); // Store the full token details
    
    // Redirect to a post-login page (e.g., dashboard)
    header('Location: dashboard.php'); // or another page
    exit();
} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    exit('Error getting access token: ' . $e->getMessage());
}
?>
