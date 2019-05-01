<?php
include __DIR__ . '/../src/autoload.php';
session_start();

$config = include __DIR__ . '/config.php';

if (isset($_GET['code'], $provider))
{
    $settings = $config[$provider];

    $client = new \WebAuth\Client($provider);
    $client->setClientId($settings['client_id']);
    $client->setClientSecret($settings['client_secret']);
    $client->setRedirectUri($settings['redirect_uri']);

    $client->setCode($_GET['code']);

    try {
        if (!$client->getAccessToken()) {
            $client->requestAccessToken();
        }
        $client->requestValidateToken();
        $client->requestIdentity();

        header(sprintf("Location: %s/demo.php?provider=%s", dirname($_SERVER['PHP_SELF']), $provider));

    } catch (\WebAuth\Exception $e) {
        echo $e->getMessage();
    }
}