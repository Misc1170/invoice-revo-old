<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = require_once __DIR__ . '/config.php';
$storage_config = $config['file_storage'];
$db_config = $config['databases']['main'];

require_once __DIR__ . '/StorageDbService.php';
require_once __DIR__ . '/StorageFileService.php';

$DbService = new StorageDatabase(
    $db_config['host'],
    $db_config['port'],
    $db_config['user'],
    $db_config['password'],
    $db_config['db']
); 

$FileService = new StorageFileService(
    $storage_config['bucketName']
);