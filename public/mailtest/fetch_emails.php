<?php
require 'config.php';
require 'vendor/autoload.php';

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

// Ensure the session is started
session_start();

if (!isset($_SESSION['accessToken'])) {
    header('Location: index.php'); // Redirect to login if no token
    exit();
}

// Create a Graph client
$graph = new Graph();
$graph->setAccessToken($_SESSION['accessToken']);

// Fetch the last 10 emails from the user's inbox
try {
    $response = $graph->createRequest('GET', '/me/messages?$top=10&$select=receivedDateTime,sender,subject,body')
                      ->setReturnType(Model\Message::class)
                      ->execute();
    
    $emails = $response->getBody();
} catch (Exception $e) {
    echo 'Error fetching emails: ' . $e->getMessage();
    exit();
}
