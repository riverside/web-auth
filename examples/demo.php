<?php
include __DIR__ . '/../src/autoload.php';
session_start();

$config = include __DIR__ . '/config.php';

if (isset($_GET['action']) && $_GET['action'] == 'logout')
{
    $_SESSION = array();
    header('Location: index.php');
    exit;
}

if (isset($_GET['provider']))
{
    $provider = $_GET['provider'];
    $settings = $config[$provider];

    $client = new \WebAuth\Client($provider);
    $client->setClientId($settings['client_id']);
    $client->setClientSecret($settings['client_secret']);
    $client->setRedirectUri($settings['redirect_uri']);

    if ($identity = $client->getIdentity())
    {
        ?>Hi, <?php echo $identity->getDisplayName(); ?> <a href="demo.php?action=logout">Log Out</a><?php
    } else {
        ?><a href="<?php echo $client->getAuthUrl(); ?>">Log In</a><?php
    }
}

