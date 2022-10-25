<?php

function parseQuery($query){
    $parse = explode("&", trim($query, "?"));
    $result = array();
    foreach ($parse as $item){
        $item = explode("=", $item);
        $result[$item[0]] = $item[1];
    }
    return $result;
}

$config = require_once __DIR__ . '/config.php';
$db_config = $config['databases']['main'];

$mysqli = new mysqli(
    $db_config['host'] . ':' . $db_config['port'],
    $db_config['user'], 
    $db_config['password'], 
    $db_config['db']
);

if (mysqli_connect_errno())
    printf("Connect failed: %s\n", mysqli_connect_error());

$sql = 'SELECT `id`, `pay` FROM `invoice` WHERE `pay` != ""';
$query = $mysqli->query($sql);
$fetch = $query->fetch_all(MYSQLI_ASSOC);

foreach ($fetch as $row){
    $query = parseQuery($row['pay']);
    $InvoiceId = $query['InvoiceId'];
    $sql = 'UPDATE `pdf_uploads` SET `InvoiceId` = ' . $InvoiceId . ' WHERE `id` = ' . $row['id'];
    if($InvoiceId)
        $mysqli->query($sql);
}
