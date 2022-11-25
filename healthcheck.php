<?php

$isHealthy = true; // true - server can recieve responses from Load Balancer. false - can`t.

if($isHealthy){
    http_response_code(200);
} else {
    http_response_code(500);
}