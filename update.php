<?php

header('Content-Type: application/json');
$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

$config = require_once __DIR__ . '/config.php';
$db_config = $config['databases']['main'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli(
    $db_config['host'] . ':' . $db_config['port'],
    $db_config['user'], 
    $db_config['password'], 
    $db_config['db']
);

if (isset($data['super_is_paid'])) {
    $result = array();
    foreach ($data['data'] as $item) {
        $sql = 'UPDATE `pdf_uploads` 
                SET 
                    `InvoiceId` = "' . $item['InvoiceId'] . '",
                    `paid_percent` = "' . $item['paid_percent'] . '",
                    `paid_date` = "' . $item['paid_date'] . '",
                    `order_amount` = "' . $item['order_amount'] . '",
                    `paid_amount` = "' . $item['paid_amount'] . '",
                    `paid_detail` = "' . str_replace('"', '\"', serialize($item['paid_detail'])) . '"';

                    if(strtotime($item['contract_date']) > 0){
                        $sql .= ', `contract_date` = "' . $item['contract_date'] . '"';
                    }
                $sql .= ' WHERE `order_id` = "' . $item['order_id'] . '"';
        $result[$item['order_id']] = $mysqli->query($sql) !== false ? 1 : 0;
    }
    echo json_encode(array('result' => $result));
} else {
    $sql = 'UPDATE `pdf_uploads` SET `is_paid` = 1 WHERE `order_id` = "' . $data['order_id'] . '"';
    $result = $mysqli->query($sql);
    
    echo json_encode(array('result' => $result));
}

?>