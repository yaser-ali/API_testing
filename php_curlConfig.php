<?php

global $AuthToken, $query;

include "APIConfig.php";


$ch = curl_init('https://sandbox.api.wayfair.com/v1/graphql');
# Setup request to send json via POST.
curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:' . $AuthToken));
# Return response instead of printing.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
