<?php

    require 'C:\Users\Yaser\vendor\autoload.php';

    $client = new \GuzzleHttp\Client();

    $clientId = "pwc7yxGMsvrULTN_LOD3-Q";
    $clientSecret = "VOtlH8AafT0PMuIh8Ijr0YHi-1T9hNngOH2_c8pEL7__u3oU57PykDVYcNC4RgKqGfRczNjExYjB17ipKR83dA";

    $response = $client->post(
      'https://sso.auth.wayfair.com/oauth/token',
      [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode([
          'client_id' => $clientId,
          'client_secret' => $clientSecret,
          'audience' => 'https://sandbox.api.wayfair.com/v1/graphql',
          'grant_type' => 'client_credentials'
        ])
      ]
    );

    $tempString = substr((string)$response->getBody(),17,5000);

    $contents = substr((string)$response->getBody(),17,strpos($tempString,",",0)-1);

    //print_r($contents);

    $AuthToken = 'Bearer ' . $contents;

    $response = $client->post(
      'https://sandbox.api.wayfair.com/v1/graphql',
      [
        'headers' => [
          'Authorization' => $AuthToken,
          'Content-Type' => 'application/json',
        ],
      ]
    );

        $query = "query {
          getDropshipPurchaseOrders (
            limit: 5,
            hasResponse: false,
            fromDate: \"2022-01-01\",
          ) {
            poNumber,
            poDate,
            estimatedShipDate,
            customerName,
            customerAddress1,
            customerAddress2,
            customerCity,
            customerState,
            customerPostalCode,
            orderType,
            shippingInfo {
              shipSpeed,
              carrierCode
            },
            packingSlipUrl,
            warehouse {
              id,
              name,
              address {
                name,
                address1,
                address2,
                address3,
                city,
                state,
                country,
                postalCode
              }
            },
            products {
              partNumber,
              quantity,
              price,
              event {
                id,
                type,
                name,
                startDate,
                endDate
              }
            },
            shipTo {
              name,
              address1,
              address2,
              address3,
              city,
              state,
              country,
              postalCode,
              phoneNumber
              }
            }
        }";

        $data = array('query' => $query);
        $query = json_encode($data);

        $ch = curl_init( 'https://sandbox.api.wayfair.com/v1/graphql' );
        # Setup request to send json via POST.
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:' . $AuthToken));
        # Return response instead of printing.
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        # Send request.
        $result = curl_exec($ch);

        curl_close($ch);
        # Print response.
        echo "<p style='width: 50%; word-wrap: break-word;'>$result</p>";
		
