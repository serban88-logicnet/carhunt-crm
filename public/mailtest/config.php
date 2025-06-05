<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = [
    'appId' => 'fdb4d627-8796-4ab4-9a0a-ca989814a698',
    'appSecret' => 'IRI8Q~tYZUNUkXUlyV9zFYDNTVa0ecpYFyutebjQ',
    'redirectUri' => 'https://crm-dev.logicnet.ro/mailtest/callback.php',
    'tenantId' => 'common',
    'scopes' => 'openid profile offline_access user.read mail.readwrite'
];
?>
