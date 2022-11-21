<?php

$config = require_once __DIR__ . '/config.php';

if($config['isHealthy']){
    http_response_code(200);
} else {
    http_response_code(500);
}