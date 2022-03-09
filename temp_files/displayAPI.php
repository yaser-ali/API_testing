<?php

function displayAPI()
{
if (!empty($query)) {
$data = array('query' => $query);
}

$query = json_encode($data);

if (!empty($output)) {
$POArray = json_decode($output);
$POOrders = json_encode($POArray->data->getDropshipPurchaseOrders);
$POArray = json_decode($POOrders , true);
}

foreach ($POArray as $item) {
// In the DB
// Print response.
echo "Name: " . $item[ 'customerName' ] . "</br>";
echo "PO number: " . $item[ 'poNumber' ] . "</br>";
echo "City: " . $item[ 'customerCity' ] . "</br>";
echo "PO date: " . $item[ 'poDate' ] . "</br>";
echo "Estimated ship date: " . $item[ 'estimatedShipDate' ] . "</br>";
echo "shipSpeed: " . $item[ 'shippingInfo' ][ 'shipSpeed' ] . "</br>";
echo "carrier code: " . $item[ 'shippingInfo' ][ 'carrierCode' ] . "</br>";
echo "packingSlipUrl: " . $item[ 'packingSlipUrl' ] . "</br></br>";
//                    echo "warehouse ID: " . $item['warehouse']['id'] . "</br>";
//                    echo "warehouse Name: " . $item['warehouse']['name'] . "</br>";
//                    echo "Address Name: " . $item['warehouse']['address']['name'] . "</br>";
//                    echo "Address 1: " . $item['warehouse']['address']['address1'] . "</br>";
//                    echo "Address 2: " . $item['warehouse']['address']['address2'] . "</br>";
//                    echo "Address 3: " . $item['warehouse']['address']['address3'] . "</br>";
//                    echo "City: " . $item['warehouse']['address']['city'] . "</br>";
//                    echo "State: " . $item['warehouse']['address']['state'] . "</br>";
//                    echo "Country: " . $item['warehouse']['address']['country'] . "</br>";
//                    echo "PostalCode: " . $item['warehouse']['address']['postalCode'] . "</br>";
//                    echo "product part number: " . $item['products'][0]['partNumber'] . "</br>";
//                    echo "quantity: " . $item['products'][0]['quantity'] . "</br>";
//                    echo "price: " . $item['products'][0]['price'] . "</br>";
//                    echo "event: " . $item['products'][0]['event'] . "</br>";
//                    echo "shipName: " . $item['shipTo']['name'] . "</br>";
//                    echo "address1: " . $item['shipTo']['address1'] . "</br>";
//                    echo "address2: " . $item['shipTo']['address2'] . "</br>";
//                    echo "address3: " . $item['shipTo']['address3'] . "</br>";
//                    echo "city: " . $item['shipTo']['city'] . "</br>";
//                    echo "state: " . $item['shipTo']['state'] . "</br>";
//                    echo "country: " . $item['shipTo']['country'] . "</br>";
//                    echo "postalCode: " . $item['shipTo']['postalCode'] . "</br>";
//                    echo "phoneNumber: " . $item['shipTo']['phoneNumber'] . "</br></br>";
}

if (!empty($ch)) {
curl_close($ch);
}

// Inputs the json data into a text file
$file = 'testFile.txt';
// Open the file to get existing content
$current = file_get_contents($file);
// Adds data to the file
$current = json_encode($output);
// Write the contents back to the file
file_put_contents($file , $current);
}