<?php

require_once __DIR__ . '/Src/init.php';
$mysqli = $DbService->getConnection();

$sql = 'SELECT `id`, `path` FROM `pdf_uploads` WHERE `unzip` = 1 AND (`lastAction` + INTERVAL 5 MINUTE) < NOW()';
$query = $mysqli->query($sql);

if($query->num_rows < 1)
    return false;

$fetch = $query->fetch_all(MYSQLI_ASSOC);

foreach ($fetch as $row) {

    $path = __DIR__ . '/upload-pdfs/files/' . trim($row['path'], '\\/');
    $explode = explode('/', $path);
    array_pop($explode);
    $path = implode('/', $explode) . '/unzipped/';
    $files = is_dir( $path ) ? array_slice(scandir($path), 2) : array();

    if(empty($files)){
        continue;
    }

    // Архивы лежат в S3 бакете, поэтому можно удалить локально скачанные
    unlink($path);

    // Удаляем разархивированные файлы
    foreach($files as $file){
        if(file_exists($path . $file)){
            unlink($path . $file);
            $mysqli->query('UPDATE `pdf_uploads` SET `unzip` = 0 WHERE `id` = ' . $row['id']);
        }

    }
}
