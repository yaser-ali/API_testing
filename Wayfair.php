<?php


include 'APIWayfairClass.php';

global $autoID, $query, $output, $echo;


class ChildAPI extends getAPI
{
    public function DownloadRunMethod()
    {
        $this->DownloadRun();
    }
    public function BreakDownDownloadRunMethod()
    {
        $this->BreakDownDownloadRun();
    }
    public function DownloadAllLabelsMethod()
    {
        $this->DownloadAllLabels();
    }
}

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
    case array_key_exists('DownloadLabels', $_POST):
        DownloadLabels();
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

function DownloadLabels()
{
    $obj = new ChildAPI();
    $obj->DownloadAllLabelsMethod();
}

function refresh()
{
    header("refresh:0");
    session_destroy();
}

?>
<head>
    <title>
        Wayfair
    </title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" crossorigin="anonymous"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="css/format.css">

    <script src="js/events.js"></script>
    <style>
    .col-sm-6 {
      display: block;
    }
    .col-sm-10 {
      display: block;
    }
    </style>
</head>

<?php


if (in_array($_SERVER['REQUEST_METHOD'],array("GET","POST", "DELETE"))) {
    echo '<div class="loader"></div>';
    echo '<button style="float: right;" type="button" data-toggle="modal" data-target="#myModal">Toggle</button>';
}
?>
<br>
<br>
<h1 align='center'>Wayfair</h1>

<body class="p-4 bg-light text-dark">
<div align="center">
<div style="padding: 10px; display: inline-flex;">

<form method="post">

<?php
echo '<input class="btn btn-success" type="submit" name="Download" value="Download Orders" />';
?>



<input type="button" class="btn btn-success" onclick="window.location.href='index.php'" value="Back to menu">

</form>

</div>
</div>

<?php
if (isset($_SESSION['autoID'])) {
    echo '  <!-- Modal -->
      <div class="modal fade" id="myModal" role="dialog">
        <div class="modal-dialog modal-lg">

          <!-- Modal content-->
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
              <div class="modal-body">
              '.$_SESSION['echo'].'
              </div>
          </div>
          </br>
        </div>
      </div>';
}
else {
    echo "";
}
?>

<div>
<?php
    $obj = new ChildAPI();
    $obj->BreakDownDownloadRunMethod();
?>
</div>


</body>
