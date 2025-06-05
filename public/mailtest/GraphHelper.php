<?php
require_once 'vendor/autoload.php';
require_once 'config.php';

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;

class GraphHelper {
    private static Graph $graph;
    private static AccessToken $accessToken;

    public static function initializeGraphForUserAuth(): void {
        global $config;

        $provider = new GenericProvider([
            'clientId'                => $config['appId'],
            'clientSecret'            => $config['appSecret'],
            'redirectUri'             => $config['redirectUri'],
            'urlAuthorize'            => 'https://login.microsoftonline.com/' . $config['tenantId'] . '/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/' . $config['tenantId'] . '/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                  => explode(' ', $config['scopes'])
        ]);

        // Retrieve the access token from session or other storage
        if (isset($_SESSION['accessToken'])) {
            // Assume $_SESSION['accessToken'] contains the array
            self::$accessToken = new AccessToken($_SESSION['accessToken']);
        } else {
            throw new Exception('Access token is missing.');
        }

        self::$graph = new Graph();
        self::$graph->setAccessToken(self::$accessToken->getToken());
    }

    public static function getUser(): Model\User {
        return self::$graph->createRequest('GET', '/me')
            ->setReturnType(Model\User::class)
            ->execute();
    }

    public static function getInbox(): Model\MessageCollectionResponse {
        return self::$graph->createRequest('GET', '/me/messages?$top=10&$select=receivedDateTime,sender,subject,bodyPreview')
            ->setReturnType(Model\MessageCollectionResponse::class)
            ->execute();
    }
}
?>
