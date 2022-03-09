<!DOCTYPE html>
<html lang="en">
<head>
    <title>
        CastleGate
    </title>

    <link rel="stylesheet" href="styles/Styles.css" type="text/css">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" crossorigin="anonymous"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" crossorigin="anonymous"></script>

<style>

.tabs {
  position: relative;
  min-height: 200px;
  clear: both;
  margin: 25px 0;
}

.tab {
  float: left;
}

.tab label {
  background: #eee;
  padding: inherit;
  border: 1px solid #ccc;
  position: relative;
  left: 1px;
}

.tab [type=radio] {
  display: none;
}

.content {
  position: absolute;
  top: 28px;
  left: 0;
  right: 0;
  bottom: 0;
  /*padding: 20px;*/
  margin-right: 10px;
  /*border: 1px solid black;*/
}

[type=radio]:checked ~ label {
  background: white;
  border-bottom: 1px solid white;
  z-index: 2;
}

[type=radio]:checked ~ label ~ .content {
  z-index: 1;
}


</style>

</head>
<h1 align='center'>Castle Gate</h1>


<body class="p-4 mb-2 bg-light text-dark">

<div class="menu" align="center">

<form method="post">
<input class="btn btn-info" type="submit" name="Download" value="Download Orders"/>


</form>
</br>
<button class="btn btn-dark" onclick="window.location.href='index.php'">Back to menu</button>

</div>


<?php

include "DBConfig/DBFile.php";

global $DwnRun;


class getAPI
{

    function DownloadRun()
    {
        global $conn, $output, $query, $ch, $success;

        $getPOQuery = 'query getCastleGateDropshipPurchaseOrders {
            getDropshipPurchaseOrders (
                hasResponse: false
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
        }';

        $data = array('query' => $getPOQuery);
        $query = json_encode($data);

        include "php_curlConfig.php";

        $output = curl_exec($ch);

        $POArray = json_decode($output);
        $POOrders = json_encode($POArray->data->getDropshipPurchaseOrders);
        $POArray = json_decode($POOrders , true);

        $datetoday = new datetime();
        $date = $datetoday->format("dmY");

        $unix = (microtime());
        $unixStr = substr($unix, 19);


        foreach ($POArray as $item) {
                $qry = odbc_prepare($conn , "insert into getDropshippingTables (poNumber, customerName,
                    customerCity, customerAddress1, customerAddress2, customerState, customerPostalCode, poDate, estimatedShipDate,
                    orderType, shipSpeed, carrierCode, packingSlipUrl, warehouseID, warehouseName,
                    addressname, address1, address2, address3, city, state, country, postalcode, partNumber, quantity, price, phoneNumber, DownloadRun)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");

                $country = $item['shipTo']['country'];
                //Download run string identifier
                $downloadRunString = $country . $date . $unixStr;

                $success = odbc_execute($qry , array($item[ 'poNumber' ] , $item[ 'customerName' ] , $item[ 'customerCity' ] , $item[ 'customerAddress1' ] , $item[ 'customerAddress2' ] ,
                    $item[ 'customerState' ] , $item[ 'customerPostalCode' ] , $item[ 'poDate' ] , $item[ 'estimatedShipDate' ] , $item[ 'orderType' ] ,
                    $item[ 'shippingInfo' ][ 'shipSpeed' ] , $item[ 'shippingInfo' ][ 'carrierCode' ] , $item[ 'packingSlipUrl' ] , $item[ 'warehouse' ][ 'id' ] , $item[ 'warehouse' ][ 'name' ] , $item[ 'warehouse' ][ 'address' ][ 'name' ] ,
                    $item[ 'warehouse' ][ 'address' ][ 'address1' ] , $item[ 'warehouse' ][ 'address' ][ 'address2' ] , $item[ 'warehouse' ][ 'address' ][ 'address3' ] ,
                    $item[ 'warehouse' ][ 'address' ][ 'city' ] , $item[ 'warehouse' ][ 'address' ][ 'state' ] , $item[ 'warehouse' ][ 'address' ][ 'country' ] ,
                    $item[ 'warehouse' ][ 'address' ][ 'postalCode' ] , $item[ 'products' ][ 0 ][ 'partNumber' ] , $item[ 'products' ][ 0 ][ 'quantity' ] , $item[ 'products' ][ 0 ][ 'price' ] , $item[ 'shipTo' ][ 'phoneNumber' ], $downloadRunString));
        }

        if (!$success)
        {
            echo "No data has been received from API";
        }
        else 
        {
            // $updatequery = odbc_prepare($conn, "Update getDropshippingTables Set Accepted = 1, register = 1, dispatch = 1");
            // odbc_execute($updatequery);
            header("Refresh:0; url=Wayfair.php");
            ob_start();
        }

    }


    function BreakDownDownloadRun()
    {
        //SQL query for getting each specific download run data for every PO number that has been downloaded.
        $SQLQuery = "Select Distinct DownloadRun From getDropshippingTables";

        //execute the query
        global $conn, $DwnRun, $result;

        $rs = odbc_exec($conn , $SQLQuery);

        $rows = array();

        echo '<div class="tabs">';

        if ($rs) {
           $i = 0;
            while ($row = odbc_fetch_array($rs))
            {
                $rows[] = $row;
                $i++;
            }

            foreach ($rows as $row) {
                foreach ($row as $DwnRun) {
                    //DownloadRun Tabs
                    echo '<div class="tab">';
                    echo '<input type="radio" id="' . $DwnRun  . '" name="tab-group-1" checked/>';
                    echo '<label for="' . $DwnRun  . '">'. $DwnRun  . '</label>';

                    //Content
                        echo '<div id="' . $DwnRun  . '" class="content">';
                            $this->displayPOs($DwnRun);
                        echo '</div>';
                    }
                }
            }
            echo '</div>';
            // header("Refresh:0; url=Wayfair.php");
    }


    //Display the orders that has been downloaded through the DownloadRun() function.
    function displayPOs($DwnRun)
    {

        //SQL query for getting the table's data.
        $SQLQuery = "Select * From getDropshippingTables where DownloadRun ='$DwnRun' order by partNumber asc";


        //execute the query
        global $conn, $DwnRun;

        $x = 1;

        $rs = odbc_exec($conn , $SQLQuery);


        if ($rs) {
            echo '<form action="" method="post">';
            echo '<table class="table table-responsive-sm table-dark">';
            echo "<tr style='text-align: center;'>
            <th>Select</th>
            <th>Shipping Labels</th>
            <th>PO ID</th>
            <th>PO Number</th>
            <th>Customer Name</th>
            <th>PO Date</th>
            <th>Post Code</th>
            <th>Part Number</th>
            <th>Quantity</th>
            <th>Download Run</th>
            <th>Accepted</th>
            <th>Register</th>
            <th>Dispatched</th>
            </tr>";

            while ($row = odbc_fetch_array($rs))
            {    
                    $id = $row[ 'PoID' ];
                    //Output the rows specified fields.
                    echo '<tr style="text-align: center">  <td> <input type="checkbox" id="'.$DwnRun.'" name="poNum[]" value="'.$id.'">'.$x.'</td>' . "<td>" . 

                    (file_exists("labels/" .$row[ 'poNumber' ] . ".pdf") !='<img src="img/redcross.png" width="50%">' ?: '<img src="img/tick.png" width="50%">')
                   . 
                   "<td>" . $row[ 'PoID' ] . "</td>" . "<td>" .  $row[ 'poNumber' ] . "</td>" . "<td>" .
                        $row[ 'customerName' ] . "</td>" . "<td>" . $row[ 'poDate' ] . "</td>"
                        . "<td>" . $row[ 'customerPostalCode' ]. "</td>" . "<td>"
                        . $row[ 'partNumber' ] . "</td>" . "<td>"
                        . $row[ 'quantity' ] . "</td>" . "<td>"
                        . $row[ 'DownloadRun' ] . "</td>" . "<td>"
                        . $row[ 'Accepted' ] . "</td>" . "<td>"
                        . $row[ 'register' ] . "</td>" . "<td>"
                        . $row[ 'dispatch' ] . "</td>" . "</tr>";
                        $x++;
            }

            //Accept validation.
            if (isset($_POST['AcceptSubmit'])) {
                if(!empty($_POST['poNum'])) {
                    foreach ($_POST['poNum'] as $idVal) {
                        $this->Accept($idVal);
                    }
                }
            }

            //Register validation.
            if (isset($_POST['RegisterSubmit'])) {
                if(!empty($_POST['poNum'])) {
                    foreach ($_POST['poNum'] as $idVal) {
                        $this->Register($idVal);
                    }
                }
            }

            //Dispatch validation.
            if (isset($_POST['DispatchSubmit'])) {
                if(!empty($_POST['poNum'])) {
                    foreach ($_POST['poNum'] as $idVal) {
                        $this->Dispatch($idVal);
                    }
                }
            }

            //NetWeight validation.
            if (isset($_POST['NetWeight'])) {
                if(!empty($_POST['poNum'])) {
                    foreach ($_POST['poNum'] as $idVal) {
                        $this->NetWeight($idVal);
                    }
                }
            }

            //DownloadLabels button.
            if (isset($_POST['DownloadLabels'])) {
                if(!empty($_POST['poNum'])) {
                    foreach ($_POST['poNum'] as $idVal) {
                        $this->DownloadAllLabels($idVal);
                    }

                }
            }

            echo "</table>";

            echo '<script type="text/javascript">
            //Select all checkboxes
            function toggle'.$DwnRun.'(source) {
            var checkboxes = document.querySelectorAll("input[id='.$DwnRun.']");
            for (var i=0; i<checkboxes.length; i++)
                 {
                   checkboxes[i].checked = source.checked;
                 }
            }
            </script>';

            echo '<input type="checkbox" onclick="toggle'.$DwnRun.'(this)" /> Select All</br></br>';

            echo "<input class='btn btn-success' id='stayOpen' type='submit' name='AcceptSubmit' value='Accept'>&nbsp;";
            echo "<input class='btn btn-danger' type='submit' name='RegisterSubmit' value='Register'>&nbsp;";
            echo "<input class='btn btn-warning' type='submit' name='DispatchSubmit' value='Dispatch'>&nbsp;";
            echo "<input class='btn btn-dark' type='submit' name='NetWeight' value='NetWeight'>&nbsp;";
            echo "<input class='btn btn-info' type='submit' name='Stock' value='Stock'/>&nbsp;";
            echo "<input class='btn btn-info' type='submit' name='Invoice' value='Export invoice csv'/>&nbsp;";
            echo "<input class='btn btn-info' type='submit' name='Refresh' value='Refresh'/>&nbsp;";
            echo "<input class='btn btn-info' type='submit' name='DownloadLabels' value='Download Specific Label(s)'/>&nbsp;<br>";

            echo "</br>";
            echo "</form>";
        }
        // odbc_free_result($rs);
    }

    function Accept($autoID)
    {
        //Error to be done here.

        $SQLQuery = "Select * from getDropshippingTables where PoID=$autoID";

        global $conn, $query, $output, $ch;

        $rs = odbc_exec($conn , $SQLQuery);

        if ($rs) {
                while ($row = odbc_fetch_array($rs)) {
                    $poNumber = $row[ "poNumber" ];
                    $shipSpeed = $row[ "shipSpeed" ];
                    $partNumber = $row[ "partNumber" ];
                    $quan = $row[ "quantity" ];
                    $price = $row[ "price" ];
                    $estimatedDate = $row[ "estimatedShipDate" ];
                    //Set the selected ID as accepted.
                }

        $acceptQuery = "mutation acceptOrder {purchaseOrders {accept(poNumber: \"$poNumber\",shipSpeed: $shipSpeed, lineItems: [{partNumber: \"$partNumber\", quantity: $quan, unitPrice: $price, estimatedShipDate: \"$estimatedDate\"}]){id,handle,status,submittedAt,completedAt}}}";


            //Accept the selected data into to accept query mutation.
            $data =  array('query' => $acceptQuery);
            $query = json_encode($data);

            include "php_curlConfig.php";

            $output = curl_exec($ch);
        }
                odbc_exec($conn,"Update getDropshippingTables SET Accepted='1' WHERE PoID='$autoID'");


        echo "<div class='row'>
                      <div class='col-sm-6'>
                        <div class='card'>
                          <div class='card-body'>
                            <h5 class='card-title'>ID : $autoID - Query:</h5>
                            <p class='card-text'>$query</p>
                                </div>
                        </div>
                      </div>
                      <div class='col-sm-6'>
                        <div class='card'>
                          <div class='card-body'>
                            <h5 class='card-title'>Response:</h5>
                            <p class='card-text'>$output</p>
                          </div>
                        </div>
                      </div>
                  </div>
                  </br>";
        
        # Send request.
        curl_close($ch);
        odbc_free_result($rs);
    }

    function DownloadAllLabels($autoID)
    {
        $SQLQuery = "Select * from getDropshippingTables where PoID=$autoID";

        global $conn, $query, $output, $ch, $content, $LabelQuery;

        $rs = odbc_exec($conn , $SQLQuery);

        $i = 0;

        if ($rs) {

            while ($row = odbc_fetch_array($rs)) {
                    $i++;
                    $poNumber = $row[ 'poNumber' ];
                    $wareID = $row[ 'warehouseID' ];
                    $Date = new \DateTime("tomorrow", new \DateTimeZone("UTC"));

                    $poDate = $Date->format(\DateTime::ISO8601);

                    $RegisterOrder = "mutation register {purchaseOrders {register (registrationInput: {poNumber: \"$poNumber\",warehouseId: $wareID,requestForPickupDate: \"$poDate\"}){id,eventDate,pickupDate,consolidatedShippingLabel {url},shippingLabelInfo {trackingNumber},purchaseOrder {poNumber,shippingInfo {carrierCode}}}}}";    
            }

            $data = array('query' => $RegisterOrder);
            $query = json_encode($data);

            include "php_curlConfig.php";

            $output = curl_exec($ch);

            $POArray = json_decode($output);
            $POOrders = json_encode($POArray->data->purchaseOrders);
            $POArray = json_decode($POOrders, true);
            

            //Below is an if statement which checks if the API query is being executed.
            if (is_array($POArray)) {
                //Get the PONumber shipping label url.
                $ShippingURL = "https://sandbox.api.wayfair.com/v1/shipping_label/" . $poNumber;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $ShippingURL);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl, CURLOPT_TIMEOUT, 0);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
                
                // header('Content-type: application/pdf');
                $result = curl_exec($curl);
                curl_close($curl);
                echo $result . "</br>";



                // Initialize the cURL session
                $session = curl_init($ShippingURL);
                
                $dir = "./labels/";

                $file_names = basename($ShippingURL);

                $save = $dir . $file_names . ".pdf";
    
                // Open file
                $file = fopen($save, 'wb'); 
                    
                // defines the options for the transfer
                curl_setopt($session, CURLOPT_FILE, $file); 
                curl_setopt($session, CURLOPT_HEADER, 0); 
                    
                curl_exec($session); 
                
                curl_close($session); 
                    
                fclose($file);

                $PdfFiles = "labels/" . $file_names . ".pdf";

                $LabelArray = array();

                array_push($LabelArray, $PdfFiles);

                $date = date("dm");

                foreach ($LabelArray as $label) {
                    file_put_contents('labels/merged/merged'. $date .'.pdf', $label, FILE_APPEND);
                }
            }

            else {
               echo "No labels for " . $poNumber . " (An error occured during execution)" . "</br>";
               file_put_contents('labels/bogusFile/file.pdf', fopen('https://isotropic.org/papers/chicken.pdf', 'r'));
            }

        }
        //End of DB query execution.

        odbc_free_result($rs);
        curl_close($ch);
    }

    function Register($autoID)
    {

        $SQLQuery = "Select * from getDropshippingTables where PoID=$autoID";

        global $conn, $query, $output, $ch, $content, $success;

        $rs = odbc_exec($conn , $SQLQuery);


        if ($rs) {
            while ($row = odbc_fetch_array($rs)) {
                    $poNumber = $row[ 'poNumber' ];
                    $wareID = $row[ 'warehouseID' ];
            }

            $Date = new \DateTime("tomorrow", new \DateTimeZone("UTC"));

            $poDate = $Date->format(\DateTime::ISO8601);


            $RegisterOrder = "mutation register {purchaseOrders {register (registrationInput: {poNumber: \"$poNumber\",warehouseId: $wareID,requestForPickupDate: \"$poDate\"}){id,eventDate,pickupDate,consolidatedShippingLabel {url},shippingLabelInfo {trackingNumber},purchaseOrder {poNumber,shippingInfo {carrierCode}}}}}";

            $data = array('query' => $RegisterOrder);
            $query = json_encode($data);

            include "php_curlConfig.php";

            $output = curl_exec($ch);


            $POArray = json_decode($output);
            $POOrders = json_encode($POArray->data->purchaseOrders);
            $POArray = json_decode($POOrders, true);

            if (is_array($POArray)) {
            foreach ($POArray as $item) {
                $shippinglabel = $item['consolidatedShippingLabel']['url'];
                echo "Label URL: " . $shippinglabel;
                // $content = fopen($shippinglabel, 'r');
                }
            $registerSQLQuery = odbc_prepare($conn,"Update getDropshippingTables SET trackingNum=? WHERE PoID='$autoID'");
            $success = odbc_execute($registerSQLQuery, array(isset($item['shippingLabelInfo'][0]['trackingNumber'])));
            }
            else 
            {
                echo "";
            }

            if (!$success) {
                    echo "Purchase Order Number ID: " . $autoID . " - " . "Has not been registered and updated in the databaase.";
                }
                else {
                    echo "";
                    odbc_exec($conn , "Update getDropshippingTables SET register='1' WHERE PoID='$autoID'");
                }
            }


            echo "<div class='row'>
                      <div class='col-sm-6'>
                        <div class='card'>
                          <div class='card-body'>
                            <h5 class='card-title'>ID : $autoID - Query:</h5>
                            <p class='card-text'>$query</p>
                                </div>
                        </div>
                      </div>
                      <div class='col-sm-6'>
                        <div class='card'>
                          <div class='card-body'>
                            <h5 class='card-title'>Response:</h5>
                            <p class='card-text'>$output</p>
                          </div>
                        </div>
                      </div>
                  </div>
                  </br>";

            # Close curl connection.
            curl_close($ch);
            odbc_free_result($rs);
    }

    function Dispatch($autoID)
    {

        $SQLQuery = "select * from getDropshippingTables where PoID=$autoID";

        global $conn, $query, $output, $ch;

        $rs = odbc_exec($conn , $SQLQuery);

        if ($rs) {
                while ($row = odbc_fetch_array($rs))
                {
                    $poNumber = $row[ "poNumber" ];
                    $wareID = $row[ "warehouseID" ];
                    $carrierCode = $row[ 'carrierCode' ];
                    $shipSpeed = $row[ "shipSpeed" ];
                    $partNumber = $row[ "partNumber" ];
                    $trackingNum = $row[ 'trackingNum' ];
                    $custName = $row[ "customerName" ];
                    $custAddress = $row[ "customerAddress1" ];
                    $custCity = $row[ "customerCity" ];
                    $custPost = $row[ "customerPostalCode" ];
                    $custCountry = $row[ "country" ];
                    $shipDate = $row['estimatedShipDate'];
                }
                
        //Dispatch function needs to show an appropriate ship date for the dispatch query below.

        $DispatchQuery = "mutation shipment {purchaseOrders {shipment (notice: {poNumber: \"$poNumber\",supplierId: $wareID,packageCount: 1,weight: 5.12,volume: 4.65,carrierCode: \"$carrierCode\",shipSpeed: $shipSpeed,trackingNumber: \"$trackingNum\",shipDate: \"$shipDate\",sourceAddress: {name: \"$custName\",streetAddress1: \"$custAddress\",city: \"$custCity\",postalCode: \"$custPost\",country: \"$custCountry\"},destinationAddress: {name:\"$custName\",streetAddress1: \"$custAddress\",city:\"$custCity\",postalCode:\"$custPost\",country:\"$custCountry\"}, smallParcelShipments:[{package:{code:{type:TRACKING_NUMBER,value:\"$trackingNum\"},weight:5.12},items:[{partNumber: \"$partNumber\",quantity: 1}]}]}) {id,handle,status,submittedAt,completedAt,errors {key,message}}}}";

            //Accept the selected data into to accept query mutation.

            $data = array('query' => $DispatchQuery);
            $query = json_encode($data);
            include "php_curlConfig.php";

            $output = curl_exec($ch);


            $POArray = json_decode($output);
            $POOrders = json_encode($POArray->data->purchaseOrders);
            $POArray = json_decode($POOrders, true);

            if (is_array($POArray)) {
                foreach ($POArray as $po) {
                    $dispatchSQL = odbc_prepare($conn, "Update getDropshippingTables SET submittedAt=? where PoID=?");
                    $success = odbc_execute($dispatchSQL, array(isset($po[ 'submittedAt' ]) , $autoID));
                }
            }
            else {

            }

            if (!$success) {
                echo "no data has been sent";
            }
            else {
                echo "";
                odbc_exec($conn , "Update getDropshippingTables SET dispatch='1' WHERE PoID='$autoID'");
            }

            // Inputs the json data into a text file
            $file = 'dispatchLog/dispatchInfo.lock';
            $current = file_get_contents($file);
            // Adds data to the file
            $current = json_encode($output);
            // Write the contents back to the file
            file_put_contents($file , "The response: ". $current . "\r\n\r\n Query: " . $query);
        }

        echo "<div class='row'>
                      <div class='col-sm-6'>
                        <div class='card'>
                          <div class='card-body'>
                            <h5 class='card-title'>ID : $autoID - Query:</h5>
                            <p class='card-text'>$query</p>
                                </div>
                        </div>
                      </div>
                      <div class='col-sm-6'>
                        <div class='card'>
                          <div class='card-body'>
                            <h5 class='card-title'>Response:</h5>
                            <p class='card-text'>$output</p>
                          </div>
                        </div>
                      </div>
                  </div>
                  </br>";
           # Send request.
           curl_close($ch);
           odbc_free_result($rs);
    }

    function NetWeight($autoID)
    {
      $SQLQuery = "Select a.poNumber, a.warehouseID, a.warehouseName, a.carrierCode, a.shipSpeed, a.partNumber, a.trackingNum, a.customerName, a.customerAddress1, a.customerCity, a.customerPostalCode, a.country, a.quantity, a.addressname, a.city, a.postalcode, a.country, a.customerState,

      NetWeight = (a.quantity*(Select NET_WEIGHT From [API].[dbo].[QV_STPRODMASTER] where Account = 'BEDMAKER' AND CODE = a.partNumber)),

      NetPackage = (a.quantity*(Select UNITS_PER_PACK from QV_STPRODMASTER where Account = 'BEDMAKER' AND CODE = a.partNumber)),

      Volume = (Select POWER(NET_WEIGHT, 3) From [API].[dbo].[QV_STPRODMASTER] where Account = 'BEDMAKER' AND CODE = a.partNumber)

      FROM API.dbo.getDropshippingTables a where PoID='$autoID'";

      global $conn, $ch, $output, $query, $data, $rs;

      $rs = odbc_exec($conn , $SQLQuery);

      if ($rs) {
              while ($row = odbc_fetch_array($rs))
              {
                  $poNumber = $row[ "poNumber" ];
                  $wareID = $row[ "warehouseID" ];
                  $wareName = $row['warehouseName'];
                  $carrierCode = $row[ 'carrierCode' ];
                  $shipSpeed = $row[ "shipSpeed" ];
                  $partNumber = $row[ "partNumber" ];
                  $trackingNum = $row[ 'trackingNum' ];
                  $custName = $row[ "customerName" ];
                  $custAddress = $row[ "customerAddress1" ];
                  $custCity = $row[ "customerCity" ];
                  $custPost = $row[ "customerPostalCode" ];
                  $custCountry = $row[ "country" ];
                  $custState = $row['customerState'];

                  $quantity = $row['quantity'];
                  $wareAddress = $row['addressname'];
                  $wareCity = $row['city'];
                  $warePostCode = $row['postalcode'];
                  $country = $row['country'];

                  $NetWeight = $row['NetWeight'];
                  $netPackageCount = $row['NetPackage'];
                  $volume = $row['Volume'];
              }

              $d = new datetime('tomorrow', new \DateTimeZone("UTC"));
              $estimatedShipDate = $d->format(\DateTime::ISO8601);


              $netWeightQuery = '{"query":"mutation shipment($notice: ShipNoticeInput!) {purchaseOrders {shipment(notice: $notice) {handle,submittedAt, errors {key, message}}}}",';

              $netWeightVariables = "\"variables\":{\"notice\": {\"poNumber\": \"$poNumber\",\"supplierId\": $wareID,\"packageCount\": $quantity,\"weight\": $NetWeight,\"volume\": $volume, \"carrierCode\": \"$carrierCode\",\"shipSpeed\": \"$shipSpeed\",\"trackingNumber\": \"$trackingNum\",\"shipDate\": \"$estimatedShipDate\",\"sourceAddress\": {\"name\": \"$wareName\",\"streetAddress1\": \"$wareAddress\", \"city\": \"$wareCity\",\"postalCode\":\"$warePostCode\",\"country\": \"$country\"},\"destinationAddress\": {\"name\": \"$custName\",\"streetAddress1\": \"$custAddress\",\"city\": \"$custCity\", \"state\": \"$custState\",\"postalCode\": \"$custPost\",\"country\": \"$country\"},\"smallParcelShipments\": [{\"package\": {\"code\": {\"type\": \"TRACKING_NUMBER\",\"value\": \"$trackingNum\"},\"weight\": $NetWeight},\"items\": [{\"partNumber\": \"$partNumber\",\"quantity\": $quantity}]}]}}}";


                  $data = $netWeightQuery . $netWeightVariables;
                  $query1 = json_encode($data);
                  $query = json_decode($query1);


                  include "php_curlConfig.php";

                  $output = curl_exec($ch);
        }

        echo "<div class='row'>
                  <div class='col-sm-6'>
                    <div class='card'>
                      <div class='card-body'>
                        <h5 class='card-title'>Query ID: $autoID</h5>
                        <p class='card-text'>$query</p>
                       </div>
                    </div>
                  </div>
                  <div class='col-sm-6'>
                    <div class='card'>
                      <div class='card-body'>
                        <h5 class='card-title'>Response:</h5>
                        <p class='card-text'>$output</p>
                      </div>
                    </div>
                  </div>
              </div>
              </br>";
    }

    function stockLevel()
    {
        //SQL QUERY
        $SQLQuery = "SELECT PRODUCT_CODE, physical,
                    demands = (SELECT count(QUANTITY) FROM [API].[dbo].[getDropshippingTables] WHERE partNumber = a.[PRODUCT_CODE]),
                    quantityOnHand = (a.physical - (SELECT count(QUANTITY) FROM [API].[dbo].[getDropshippingTables] WHERE partNumber = a.[PRODUCT_CODE]))
                    FROM [API].[dbo].[StockLevelTable] a where Account = 'bedmaker' and LOCATION = 'wa'";

        global $conn, $ch, $output, $query, $queryLength;

        $rs = odbc_exec($conn , $SQLQuery);

        $I = 0;
        //Once the connection and execution of query has been established.
        if($rs) {

            $stockQuery = '{"query": "mutation inventory($inventory: [inventoryInput]!) {inventory {save(inventory: $inventory,feed_kind: TRUE_UP){id,handle,status,submittedAt,completedAt}}}","variables":{"inventory": [';

                while ($row = odbc_fetch_array($rs)) {
                    $I++;

                    $partNumber = $row['PRODUCT_CODE'];
                    $quan = $row['quantityOnHand'];

                    $inventoryVar = "{\"supplierId\": 30309, \"supplierPartNumber\":\"$partNumber\", \"quantityOnHand\":$quan, \"quantityBackordered\":0, \"quantityOnOrder\":0, \"itemNextAvailabilityDate\":\"05-01-2018 00:00:00\", \"discontinued\": false}";

                    $inventoryquery = $inventoryVar . ",";
                    //Increment records.
                    $stockQuery .= $inventoryquery;
                }

            //Accept the selected data into accept query mutation.
            $data = $stockQuery . "]}}";
            $query1 = json_encode($data);
            $query = json_decode($query1);

            $query = str_replace(",]", "]", $query);
            include "php_curlConfig.php";

            $output = curl_exec($ch);
            $code = curl_getinfo($ch , CURLINFO_HTTP_CODE);

            $queryOutput = substr($query, "0", "1000");
        }

        // Inputs the json data into a text file
        $file = 'stockLog/StockInfo.lock';
        $current = file_get_contents($file);
        // Adds data to the file
        $current = json_encode($output);
        // Write the contents back to the file
        file_put_contents($file , "The response: ". $current . "\r\n\r\n Query: " . $query);

                    echo "<div class='row'>
                      <div class='col-sm-6'>
                        <div class='card'>
                          <div class='card-body'>
                            <h5 class='card-title'>Query:</h5>
                            <p class='card-text'>$queryOutput...</p>
                           </div>
                        </div>
                      </div>
                      <div class='col-sm-6'>
                        <div class='card'>
                          <div class='card-body'>
                            <h5 class='card-title'>Response:</h5>
                            <p class='card-text'>$output</p>
                          </div>
                        </div>
                      </div>
                  </div>
                  </br>";

        echo 'HTTP code: ' . $code . "</br>";
        curl_close($ch);
        odbc_free_result($rs);
    }

    function InvoiceFile()
    {
        $filename = "inv/invoice.csv";

        $delimiter = "|";

        $writeInvoice = fopen($filename, 'w');

        $fields  = array('PO_Number', 'Product' , 'Quantity', 'Price');
        fputcsv($writeInvoice, $fields, $delimiter);

        global $conn;

        $SQLQuery = "select * from getDropshippingTables";

        $rs = odbc_exec($conn , $SQLQuery);

        if ($rs) {
            while ($row = odbc_fetch_array($rs)) {
                $columnData = array($row['poNumber'], $row['partNumber'], $row['quantity'], $row['price']);
                fputcsv($writeInvoice, $columnData, $delimiter);
            }
        }
        echo "Saved";
        // readfile($filename);

        fclose($writeInvoice);
    }

}

class ChildAPI extends getAPI
{
    public function NetWeightMethod()
    {
      $this->NetWeight();
    }

    public function DownloadRunMethod()
    {
        $this->DownloadRun();
    }
    public function BreakDownDownloadRunMethod()
    {
        $this->BreakDownDownloadRun();
    }
}


$obj = new ChildAPI();
$obj->BreakDownDownloadRunMethod();
?>

<?php
switch (true)
{
    case array_key_exists('Send' , $_POST):
        Send();
        break;
    case array_key_exists('Stock' , $_POST):
        Stock();
        break;
    case array_key_exists('Invoice', $_POST):
        invoice();
        break;
    case array_key_exists('Refresh', $_POST):
        refresh();
        break;
    case array_key_exists('Download', $_POST):
        Download();
        break;
}

function Stock()
{
    $obj = new ChildAPI();
    $obj->stockLevel();
}

function invoice()
{
    $obj = new ChildAPI();
    $obj->InvoiceFile();
}
function Download()
{
    $obj = new ChildAPI();
    $obj->DownloadRunMethod();
}

function refresh()
{
    session_reset();
}


?>

</body>
</html>