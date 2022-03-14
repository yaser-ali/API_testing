<?php


include 'APIClass.php';

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
</head>

<?php 

echo '<button>T</button>';

if (in_array($_SERVER['REQUEST_METHOD'],array("GET","POST", "DELETE"))) {
    echo '<div class="loader"></div>';
}

?>

<h1 align='center'>Wayfair</h1>

<body class="p-4 bg-light text-dark">

<div class="menu" align="center">

<form method="post">

<?php 
echo '<input type="submit" name="Download" value="Download Orders" />';
?>

</form>

</br>
<button onclick="window.location.href='index.php'">Back to menu</button>
</div>

<?php
if (isset($_SESSION['autoID'])) {
    echo $_SESSION['echo'];
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
