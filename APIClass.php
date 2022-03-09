<?php
//Database configuration
include "DBConfig/DBFile.php";

//Declare Download run variable
global $DwnRun, $autoID, $query, $output;

//Turns on output buffering.
ob_start();


class getAPI
{
    function DownloadRun()
    {
        //Declare the variable
        global $conn, $output, $query, $ch, $success;

        //Graphql getDropshipPurchaseOrders query
        $getPOQuery = 'query getDropshipPurchaseOrders {
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

        //
        $data = array('query' => $getPOQuery);
        $query = json_encode($data);

        //Php curl configuration php file.
        include "php_curlConfig.php";

        //Execute the graphql query.
        $output = curl_exec($ch);

        $POArray = json_decode($output);
        $POOrders = json_encode($POArray->data->getDropshipPurchaseOrders);
        $POArray = json_decode($POOrders , true);

        $datetoday = new datetime();
        $date = $datetoday->format("dmY");

        $timeToday = new datetime();
        $unixStr = $timeToday->format("Hi");

        // echo "<a href='datalog/DataOutput.lock' target='_blank'>Download Data Output File</a>";

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
                    $item[ 'customerState' ] , $item[ 'customerPostalCode' ] , substr($item[ 'poDate' ], 0, -22) , $item[ 'estimatedShipDate' ] , $item[ 'orderType' ] ,
                    $item[ 'shippingInfo' ][ 'shipSpeed' ] , $item[ 'shippingInfo' ][ 'carrierCode' ] , $item[ 'packingSlipUrl' ] , $item[ 'warehouse' ][ 'id' ] , $item[ 'warehouse' ][ 'name' ] , $item[ 'warehouse' ][ 'address' ][ 'name' ] ,
                    $item[ 'warehouse' ][ 'address' ][ 'address1' ] , $item[ 'warehouse' ][ 'address' ][ 'address2' ] , $item[ 'warehouse' ][ 'address' ][ 'address3' ] ,
                    $item[ 'warehouse' ][ 'address' ][ 'city' ] , $item[ 'warehouse' ][ 'address' ][ 'state' ] , $item[ 'warehouse' ][ 'address' ][ 'country' ] ,
                    $item[ 'warehouse' ][ 'address' ][ 'postalCode' ] , $item[ 'products' ][ 0 ][ 'partNumber' ] , $item[ 'products' ][ 0 ][ 'quantity' ] , $item[ 'products' ][ 0 ][ 'price' ] , $item[ 'shipTo' ][ 'phoneNumber' ], $downloadRunString));
            }


        // Inputs the json data into a text file
        $file = 'datalog/DataOutput.lock';
        $current = file_get_contents($file);
        // Adds data to the file
        $current = $output;
        // Write the contents back to the file
        file_put_contents($file , "The response: ". $current);

        if ($success) 
        {
            echo '<div id="DataDisplay" class="col-sm-10">
                    <div class="card">
                      <div class="card-body">
                        <h5 class="card-title">Response:</h5>
                        <p class="card-text">'.$output.'</p>
                      </div>
                    </div>
                  </div>';
        }
        else 
        {
            echo "No data has been received from API";
        }
    }

    function BreakDownDownloadRun()
    {
        //SQL query for getting each specific download run data for every PO number that has been downloaded.
        $SQLQuery = "Select Distinct DownloadRun From getDropshippingTables where (accepted = 0 or register = 0 or dispatch = 0)";

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
                    echo '<input type="radio" id="'.$DwnRun.'" name="tab-group-'.$i.'" checked/>';
                    echo '<label for="'.$DwnRun.'">'.$DwnRun.'</label>';

                    //Content
                    echo '<div id="'.$DwnRun.'" class="content">';
                        $this->displayPOs($DwnRun);
                    echo '</div>';
                    }
                }
            }
            echo '</div>';
    }

    //Display the orders that has been downloaded through the DownloadRun() function.
    function displayPOs($DwnRun)
    {
        //execute the query
        global $conn, $DwnRun;

        //SQL query for getting the table's data.
        $SQLQuery = "Select * From getDropshippingTables where DownloadRun ='$DwnRun' and Dispatch='0' order by partNumber asc";

        $x = 1;

        $rs = odbc_exec($conn , $SQLQuery);

        echo '<script type="text/javascript">
           function toggle'.$DwnRun.'(source) {
       
           var checkboxes = document.querySelectorAll("input[id=po'.$DwnRun.']");
       
           for (var i=0; i<checkboxes.length; i++)
           {
                   checkboxes[i].checked = source.checked;

           }
        }
        </script>';

        if ($rs) {
            echo '<table class="table table-dark">';
            echo '<tr style="text-align: center;">';
            echo '<th>Select</th>
                    <th>SL</th>
                    <th>POID</th>
                    <th>PONumber</th>
                    <th>CustomerName</th>
                    <th>PODate</th>
                    <th>PostCode</th>
                    <th>Code</th>
                    <th>Quantity</th>
                    <th>DownloadRun</th>
                    <th>Accepted</th>
                    <th>Register</th>
                    <th>Dispatched</th>
                </tr>';

            echo '<form action="?='.$DwnRun.'" method="post">';

            while ($row = odbc_fetch_array($rs))
            {
                $id = $row[ 'PoID' ];
                $poNumber = $row['poNumber'];
                $file_exists = file_exists("labels/" . $poNumber . ".pdf");
                $result = $file_exists ? '<img src="img/tick.png" style="display:block; width:20px;"/>' : '<img src="img/cross.png" style="display:block; width:20px;"/>';
                
                //Output the rows specified fields.
                echo "<tr style='text-align: center'>" . '<td> <input type="checkbox" id="po'.$DwnRun.'" name="poNum[]" value="'.$id.'">'.$x.'</td>'. "<td>" . $result . "</td>" . "<td>" . $row[ 'PoID' ] . "</td>" . "<td>" .  $row[ 'poNumber' ] . "</td>" . "<td>" . $row[ 'customerName' ] . "</td>" . "<td>" . $row[ 'poDate' ] . "</td>" . "<td>" . $row[ 'customerPostalCode' ]. "</td>" . "<td>" . $row[ 'partNumber' ] . "</td>" . "<td>" . $row[ 'quantity' ] . "</td>" . "<td>" . $row[ 'DownloadRun' ] . "</td>" . "<td>" . $row[ 'Accepted' ] . "</td>" . "<td>" . $row[ 'register' ] . "</td>" . "<td>" . $row[ 'dispatch' ] . "</td>" . "</tr>";
                $x++;
            }
            echo "</table>";


            //issues with the buttons causing the div element to overflow.
            echo '<input type="checkbox" onclick="toggle'.$DwnRun.'(this)" /> Select All';
            
            echo '<table style="width:67%"><tr>';

            echo "<th><input class='btn btn-success' type='submit' name='AcceptSubmit' value='Accept'></th>";
            echo "<th><input class='btn btn-success' type='submit' name='RegisterSubmit' value='Register'></th>";
            echo "<th><input class='btn btn-success' type='submit' name='DispatchSubmit' value='Dispatch'></th>";
            echo "<th><input class='btn btn-success' type='submit' name='DeleteRecord' value='Delete record'/></th>";
            echo "<th><input class='btn btn-success' type='submit' name='Refresh' value='Refresh'/></th>";
            echo "<th><input type='submit' name='Stock' value='Stock'/></th>";
            echo "<th><input type='submit' name='Invoice' value='Export invoice csv'/></th>";
            echo "<th><input type='submit' name='DownloadLabels' value='Download Labels'/></th>";
            echo "</form>";
            echo '</tr></table>';

            echo "</br>";

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
                //Delete record validation.
                if (isset($_POST['DeleteRecord'])) {
                    if(!empty($_POST['poNum'])) {
                        foreach ($_POST['poNum'] as $idVal) {
                            $this->DeleteRecord($idVal);
                        }
                    }
                }
        }
    }

    //Delete a record.
    function DeleteRecord($autoID) {

        global $conn, $SQLQuery, $DeleteSQLQuery, $rs;

        //Selecting a record to delete a specific label.
        $SQLQuery = "Select poNumber from getDropshippingTables where PoID=$autoID";

        $res = odbc_exec($conn, $SQLQuery);

        while ($row = odbc_fetch_array($res)) {
            $poNumber = $row['poNumber'];
        }
        unlink("./labels/" . $poNumber . ".pdf");
        //End

        //Deleting the specified record.
        $DeleteSQLQuery = "Delete from getDropshippingTables where PoID=$autoID";

        $rs = odbc_exec($conn , $DeleteSQLQuery);

        if ($rs) {
            echo "<div class='row'>
                    <div class='col-sm-6'>
                        <div class='card'>
                          <div class='card-body'>
                            <h5 class='card-title'>Deleted ID : $autoID</h5>
                             </div>
                        </div>
                    </div>
                 </div>";
        }
        odbc_free_result($rs);
        //End
        header("Location: Wayfair.php");
    }

    //Accept PO.
    function Accept($autoID)
    {
        //Start a session.
        // session_start();

        $SQLQuery = "Select * from getDropshippingTables where PoID='$autoID' and Accepted='0'";

        global $conn, $query, $output, $ch, $DwnRun;

        $rs = odbc_exec($conn , $SQLQuery);

        if ($rs) {
                while ($row = odbc_fetch_array($rs)) {
                    $poNumber = $row[ "poNumber" ];
                    $shipSpeed = $row[ "shipSpeed" ];
                    $partNumber = $row[ "partNumber" ];
                    $quan = $row[ "quantity" ];
                    $price = $row[ "price" ];
                    $estimatedDate = $row[ "estimatedShipDate" ];
                }

        $acceptQuery = "mutation acceptOrder {purchaseOrders {accept(poNumber: \"$poNumber\",shipSpeed: $shipSpeed, lineItems: [{partNumber: \"$partNumber\", quantity: $quan, unitPrice: $price, estimatedShipDate: \"$estimatedDate\"}]){id,handle,status,submittedAt,completedAt}}}";


            //Accept the selected data into to accept query mutation.
            $data =  array('query' => $acceptQuery);
            $query = json_encode($data);

            include "php_curlConfig.php";

            $output = curl_exec($ch);

            odbc_exec($conn,"Update getDropshippingTables SET Accepted='1' WHERE PoID='$autoID'");

        }
        else {
            header("Refresh:0");
        }

        $_SESSION['autoID'] = $autoID;
        // $_SESSION['query'] = $query;
        // $_SESSION['output'] = $output;



         echo '<!-- The Modal -->
                <div id="myModal" class="modal">

                  <!-- Modal content -->
                  <div class="modal-content">
                    <span class="close">&times;</span>
                        <p> '.$_SESSION['autoID'].' </p>
                  </div>

                </div>

                <script>
                // Get the modal
                var modal = document.getElementById("myModal");

                // Get the button that opens the modal
                var btn = document.getElementById("myBtn");

                // Get the <span> element that closes the modal
                var span = document.getElementsByClassName("close")[0];

                // When the user clicks the button, open the modal 
                btn.onclick = function() {
                  modal.style.display = "block";
                }

                // When the user clicks on <span> (x), close the modal
                span.onclick = function() {
                  modal.style.display = "none";
                }

                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function(event) {
                  if (event.target == modal) {
                    modal.style.display = "none";
                    }
                }
                </script>';


       


        // echo '<div id="'.$DwnRun.'" class="row">
        //               <div class="col-sm-6">
        //                 <div class="card">
        //                   <div class="card-body">
        //                     <h5 class="card-title">ID : '.$autoID.' - Query:</h5>
        //                     <p class="card-text">'.$query.'</p>
        //                      </div>
        //                 </div>
        //               </div>
        //               <div class="col-sm-6">
        //                 <div class="card">
        //                   <div class="card-body">
        //                     <h5 class="card-title">Response:</h5>
        //                     <p class="card-text">'.$output.'</p>
        //                   </div>
        //                 </div>
        //               </div>
        //           </div>
        //           </br>';

        curl_close($ch);
        odbc_free_result($rs);
    }

    //Download labels.
    function DownloadAllLabels()
    {
        include "autoload.php";

        $pdf = new \Jurosh\PDFMerge\PDFMerger;

        //Scan the label's directory and then combine all the PO pdfs into one pdf into the merged folder.
        $AllFiles = glob("./labels/*.pdf");

        $date = date("dm");
        
        $fileget = "labels/merged/merged" . $date . ".pdf";


        foreach ($AllFiles as $label) {
            if (file_exists($label)) {
                $pdf->AddPDF($label, 'All');
            }
            else {
                echo "no labels were found";
            }
        }
        $pdf->merge('download', $fileget);
    }

    //Register PO.
    function Register($autoID)
    {
        $SQLQuery = "Select * from getDropshippingTables where PoID=$autoID AND Register=0";

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
                //$ShippingURL = "https://sandbox.api.wayfair.com/v1/shipping_label/" . $poNumber;
				$ShippingURL = "https://www.soundczech.cz/temp/lorem-ipsum.pdf";
				
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $ShippingURL);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($curl, CURLOPT_TIMEOUT, 0);
                curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0);
                
                // header('Content-type: application/pdf');
                $result = curl_exec($curl);
                curl_close($curl);
                //Outputs the response of fetching the pdf urls.
                // echo $result . "</br>";

                // Initialize the cURL session
                $session = curl_init($ShippingURL);
                
                $dir = "./labels/";

                $file_names = basename($ShippingURL);
				
                //$save = $dir . $file_names . ".pdf";
                $save = $dir . $poNumber . ".pdf";
    
                // Open file
                $file = fopen($save, 'wb'); 
                    
                // defines the options for the transfer
                curl_setopt($session, CURLOPT_FILE, $file); 
                curl_setopt($session, CURLOPT_HEADER, 0); 
                    
                curl_exec($session); 
                
                curl_close($session); 
                    
                fclose($file);

                $registerSQLQuery = odbc_prepare($conn,"Update getDropshippingTables SET trackingNum=? WHERE PoID='$autoID'");
                $success = odbc_execute($registerSQLQuery, array($item['shippingLabelInfo'][0]['trackingNumber']));
                }
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
                    header("Refresh: 30;");
                }
                header("Location: Wayfair.php");
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

    //Dispatch PO.
    function Dispatch($autoID)
    {

      $SQLQuery = "Select a.poNumber, a.warehouseID, a.warehouseName, a.carrierCode, a.shipSpeed, a.partNumber, a.trackingNum, a.customerName, a.customerAddress1, a.customerCity, a.customerPostalCode, a.country, a.quantity, a.addressname, a.city, a.postalcode, a.country, a.customerState,

      NetWeight = (a.quantity*(Select NET_WEIGHT From [API].[dbo].[QV_STPRODMASTER] where Account = 'BEDMAKER' AND CODE = a.partNumber)),

      NetPackage = (a.quantity*(Select UNITS_PER_PACK from QV_STPRODMASTER where Account = 'BEDMAKER' AND CODE = a.partNumber)),

      Volume = (Select POWER(NET_WEIGHT, 3) From [API].[dbo].[QV_STPRODMASTER] where Account = 'BEDMAKER' AND CODE = a.partNumber)

      FROM API.dbo.getDropshippingTables a where PoID='$autoID'";

        global $conn, $query, $output, $ch;

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
                  
                  // $shipDate = $row['estimatedShipDate'];
                  $Date = new DateTime("now", new \DateTimeZone("UTC"));
                  $shipDate = $Date->format(\DateTime::ISO8601);
                }
                
            //Dispatch function needs to show an appropriate ship date for the dispatch query below.

              $DispatchQuery = '{"query":"mutation shipment($notice: ShipNoticeInput!) {purchaseOrders {shipment(notice: $notice) {handle,submittedAt, errors {key, message}}}}",';

              $DispatchVariables = "\"variables\":{\"notice\": {\"poNumber\": \"$poNumber\",\"supplierId\": $wareID,\"packageCount\": $quantity,\"weight\": $NetWeight,\"volume\": $volume, \"carrierCode\": \"$carrierCode\",\"shipSpeed\": \"$shipSpeed\",\"trackingNumber\": \"$trackingNum\",\"shipDate\": \"$shipDate\",\"sourceAddress\": {\"name\": \"$wareName\",\"streetAddress1\": \"$wareAddress\", \"city\": \"$wareCity\",\"postalCode\":\"$warePostCode\",\"country\": \"$country\"},\"destinationAddress\": {\"name\": \"$custName\",\"streetAddress1\": \"$custAddress\",\"city\": \"$custCity\", \"state\": \"$custState\",\"postalCode\": \"$custPost\",\"country\": \"$country\"},\"smallParcelShipments\": [{\"package\": {\"code\": {\"type\": \"TRACKING_NUMBER\",\"value\": \"$trackingNum\"},\"weight\": $NetWeight},\"items\": [{\"partNumber\": \"$partNumber\",\"quantity\": $quantity}]}]}}}";


                  $data = $DispatchQuery . $DispatchVariables;
                  $query1 = json_encode($data);
                  $query = json_decode($query1);

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
                odbc_exec($conn , "Update getDropshippingTables SET dispatch='1' WHERE PoID='$autoID'");
                // header("Location: Wayfair.php");
                if (file_exists("./labels/" . $poNumber . ".pdf")) {
                //Deletes the labels of a PO number that has been dispatched.
                    unlink("./labels/" . $poNumber . ".pdf");
                }
                else {
                    echo "";
                }
                //Delete the record in which the po number has been dispatched.
                odbc_exec($conn, "Update getDropshippingTables SET customerName=Null, customerAddress1=Null, customerAddress2=Null, customerCity=Null, customerState=Null, customerPostalCode=Null, phoneNumber=Null WHERE PoID='$autoID' and Dispatch='1'");
            }
            else {
                echo "no data has been sent";
            }

            // Inputs the json data into a text file
            $file = 'dispatchLog/dispatchInfo.lock';
            $current = file_put_contents($file, "w");
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

           curl_close($ch);
           odbc_free_result($rs);
    }

    //Checks the stock level of each part number.
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

                    $inventoryVar = "{\"supplierId\": 143756, \"supplierPartNumber\":\"$partNumber\", \"quantityOnHand\":$quan, \"quantityBackordered\":0, \"quantityOnOrder\":0, \"itemNextAvailabilityDate\":\"05-01-2018 00:00:00\", \"discontinued\": false}";

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

            // Inputs the json data into a text file
            $file = 'stockLog/StockInfo.lock';
            $current = file_get_contents($file);
            // Adds data to the file
            $current = json_encode($output);
            // Write the contents back to the file
            file_put_contents($file , "The response: ". $current . "\r\n\r\n Query: " . $query);
        }


            // echo "<a href='stockLog/StockInfo.lock' target='_blank'>Download Stock File</a>";


          file_get_contents("stockLog/StockInfo.lock");
          $fp = fopen("stockLog/StockInfo.lock", "r");
          fpassthru($fp);
          fclose($fp);
          header('Content-Type: application/octet-stream');
          header('Content-Disposition: attachment; filename="stockLog/StockInfo.lock"');
          header("Content-Length: " . filesize("stockLog/StockInfo.lock"));
          exit();


            echo "<div class='row'>
                <div class='col-sm-6'>
                  <div class='card'>
                    <div class='card-body'>
                      <h5 class='card-title'>Query:</h5>
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

         // echo 'HTTP code: ' . $code . "</br>";
         curl_close($ch);
         odbc_free_result($rs);
    }

    //Outputs an invoice file of each PO number.
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
            fclose($writeInvoice);

            $fp = fopen("inv/invoice.csv", "r");
            fpassthru($fp);
            fclose($fp);

            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="inv/invoice.csv"');
            header("Content-Length: " . filesize("inv/invoice.csv"));            
            exit();
        }
    }
}

?>