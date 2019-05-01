# web-auth

PHP authentication library based on OAuth1 and OAuth2.
Supported social providers includes Facebook, Google, Twitter, and LinkedIn.

## Installation
```
$ php composer.phar install
```
or
```
"riverside/web-auth": "1.0"
```

## Example
```php
<?php
$client = new \WebAuth\Client('Facebook');
$client
    ->setClientId($client_id)
    ->setClientSecret($client_secret)
    ->setRedirectUri($redirect_uri);

if ($identity = $client->getIdentity())
{
    echo 'Hi, '. $identity->getDisplayName() .'<a href="logout">Log Out</a>';
} else {
    echo '<a href="'. $client->getAuthUrl() .'">Log In</a>';
}
```