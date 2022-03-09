<?php
require 'C:\Users\Yaser\vendor\autoload.php';

$client = new \GuzzleHttp\Client();

$clientId = "pwc7yxGMsvrULTN_LOD3-Q";
$clientSecret = "VOtlH8AafT0PMuIh8Ijr0YHi-1T9hNngOH2_c8pEL7__u3oU57PykDVYcNC4RgKqGfRczNjExYjB17ipKR83dA";

$response = $client->post(
'https://sso.auth.wayfair.com/oauth/token',
['headers' => ['Content-Type' => 'application/json'],
'body' => json_encode(['client_id' => $clientId,
'client_secret' => $clientSecret,
'audience' => 'https://sandbox.api.wayfair.com/v1/graphql',
'grant_type' => 'client_credentials'])]
);

$tempString = substr((string)$response->getBody(), 17, 5000);

$contents = substr((string)$response->getBody(), 17, strpos($tempString, ",",0) - 1);

$AuthToken = 'Bearer ' . $contents;

$response = $client->post(
'https://sandbox.api.wayfair.com/v1/graphql',
['headers' => ['Authorization' => $AuthToken,
'Content-Type' => 'application/json',],]
);
