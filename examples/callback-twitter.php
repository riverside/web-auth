<?php
include __DIR__ . '/../src/autoload.php';
session_start();

$config = include __DIR__ . '/config.php';

if (isset($_GET['oauth_token'], $_GET['oauth_verifier']) && $_GET['oauth_token'] == $_SESSION['Twitter_OAuth_Token'])
{
    $provider = 'Twitter';
    $settings = $config[$provider];

    $client = new \WebAuth\Client($provider);
    $client->setClientId($settings['client_id']);
    $client->setClientSecret($settings['client_secret']);
    $client->setRedirectUri($settings['redirect_uri']);

    try {
        if (!$client->getAccessToken()) {
            $client->requestAccessToken($_GET['oauth_verifier']);
        }
        $client->requestIdentity();

        header(sprintf("Location: %s/demo.php?provider=%s", dirname($_SERVER['PHP_SELF']), $provider));

    } catch (\WebAuth\Exception $e) {
        echo $e->getMessage();
    }
}