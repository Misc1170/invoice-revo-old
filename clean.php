<?php

$config = require_once __DIR__ . '/config.php';
$db_config = $config['databases']['main'];

$mysqli = new mysqli(
    $db_config['host'] . ':' . $db_config['port'], 
    $db_config['user'], 
    $db_config['password'], 
    $db_config['db']
);
$sql = 'SELECT `id`, `path` FROM `pdf_uploads` WHERE `unzip` = 1 AND (`lastAction` + INTERVAL 5 MINUTE) < NOW()';
$query = $mysqli->query($sql);

if($query->num_rows < 1)
    return false;

$fetch = $query->fetch_all(MYSQLI_ASSOC);

foreach ($fetch as $row) {

    $path = str_replace('https://fluid-line.ru', 'http://revo.j743689.myjino.ru', $row['path']);
    $path = str_replace('http://' . $_SERVER['HTTP_HOST'], $_SERVER['DOCUMENT_ROOT'], $path);
    $explode = explode('/', $path);
    array_pop($explode);
    $path = implode('/', $explode) . '/unzipped/';
    $files = is_dir( $path ) ? array_slice(scandir($path), 2) : array();

    if(empty($files))
        continue;

    foreach($files as $file){
        if(file_exists($path . $file)){
            unlink($path . $file);
            $mysqli->query('UPDATE `pdf_uploads` SET `unzip` = 0 WHERE `id` = ' . $row['id']);
        }

    }
}
?>