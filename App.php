<?php

require_once "vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

$url = "https://example.net.tr/includes/api.php";
$identifier = "x";
$secret = "x";
$client = new Client(['verify' => false]);

// Get client session
$getClientParams = [
    'multipart' => [
        ['name' => 'action', 'contents' => 'GetClients'],
        ['name' => 'identifier', 'contents' => $identifier],
        ['name' => 'secret', 'contents' => $secret],
        ['name' => 'status', 'contents' => 'Active'],
        ['name' => 'responsetype', 'contents' => 'json'],
        ['name' => 'limitnum', 'contents' => '10000']
    ]
];

$getClientRes = sendRequest('POST', $url, $getClientParams);
$getClientBody = getResponseBody($getClientRes);

$getClientBodyConvert = get_object_vars(json_decode($getClientBody));

foreach ($getClientBodyConvert["clients"] as $clients) {
    foreach ($clients as $whmcsClient) {
        $getClientId = $whmcsClient->id;
        getClientsProductsService($getClientId);
    }
}

// Get client products session
function getClientsProductsService($clientId)
{
    global $url, $identifier, $secret, $client;

    $getClientsProductsParams = [
        'multipart' => [
            ['name' => 'action', 'contents' => 'GetClientsProducts'],
            ['name' => 'identifier', 'contents' => $identifier],
            ['name' => 'secret', 'contents' => $secret],
            ['name' => 'responsetype', 'contents' => 'json'],
            ['name' => 'clientid', 'contents' => $clientId]
        ]
    ];

    $getClientsProductsRes = sendRequest('POST', $url, $getClientsProductsParams);
    $getClientsProductsBody = getResponseBody($getClientsProductsRes);

    $getClientsProductsBodyConvert = get_object_vars(json_decode($getClientsProductsBody));

    foreach ($getClientsProductsBodyConvert["products"] as $products) {
        foreach ($products as $product) {
            $getProductId = $product->id;
            $valueAsInt = intval($getProductId);
            updateClientService($valueAsInt);
        }
    }
}

// Update client service session
function updateClientService($serviceId)
{
    global $url, $identifier, $secret, $client;

    $updateClientProductParams = [
        'multipart' => [
            ['name' => 'action', 'contents' => 'UpdateClientProduct'],
            ['name' => 'identifier', 'contents' => $identifier],
            ['name' => 'secret', 'contents' => $secret],
            ['name' => 'serviceid', 'contents' => $serviceId],
            ['name' => 'autorecalc', 'contents' => true]
        ]
    ];

    $updateClientProductRes = sendRequest('POST', $url, $updateClientProductParams);
    echo $updateClientProductRes->getBody();
}

// Function to send HTTP request
function sendRequest($method, $url, $params)
{
    global $client;
    $request = new Request($method, $url);
    return $client->sendAsync($request, $params)->wait();
}

// Function to extract response body from Guzzle response
function getResponseBody($response)
{
    return $response->getBody();
}

?>
