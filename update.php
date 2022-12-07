<?php

header('Content-Type: application/json');
$postData = file_get_contents('php://input');

$input_log_dir = __DIR__ . "/logs/updates/requests/" . date("Y-m-d");
if(!is_dir($input_log_dir)){
    mkdir($input_log_dir, 0755, true);
}

$output_log_dir = __DIR__ . "/logs/updates/results/" . date("Y-m-d");
if(!is_dir($output_log_dir)){
    mkdir($output_log_dir, 0755, true);
}

$log_file = uniqid(date("H-i-s") . '_') . '.log';
file_put_contents($input_log_dir . '/' . $log_file, $postData);

$data = json_decode($postData, true);

require_once __DIR__ . '/Src/init.php';
$mysqli = $DbService->getConnection();

if (isset($data['super_is_paid'])) {
    $result = array();

    foreach ($data['data'] as $item) {
        $sql = $mysqli->prepare('
            UPDATE `pdf_uploads` 
            SET 
                `InvoiceId` = ?,
                `paid_percent` = ?,
                `paid_date` = ?,
                `order_amount` = ?,
                `paid_amount` = ?,
                `paid_detail` = ?,
                `contract_date` = ?
            WHERE `order_id` = ?'
        );

        $contract_date = strtotime($item['contract_date']) > 0 ? $item['contract_date'] : null;
        $paid_detail = str_replace('"', '\"', serialize($item['paid_detail']));
        $sql->bind_param('isssssss', 
            $item['InvoiceId'], 
            $item['paid_percent'], 
            $item['paid_date'],
            $item['order_amount'],
            $item['paid_amount'],
            $paid_detail,
            $contract_date,
            $item['order_id']
        );

        $sql->execute();

        $result[$item['order_id']] = 1;

        file_put_contents($output_log_dir."/".$log_file, "
        \n
            UPDATE `pdf_uploads` 
            SET 
                `InvoiceId` = $item[InvoiceId],
                `paid_percent` = '$item[paid_percent]',
                `paid_date` = '$item[paid_date]',
                `order_amount` = '$item[order_amount]',
                `paid_amount` = '$item[paid_amount]',
                `paid_detail` = '$paid_detail',
                `contract_date` = '$contract_date'
            WHERE `order_id` = '$item[order_id]'
        \n
        ");
    }

    echo json_encode(array('result' => $result));

} else {
    
    $sql = $mysqli->prepare('UPDATE `pdf_uploads` SET `is_paid` = 1 WHERE `order_id` = ?');
    $sql->bind_param('s', $data['order_id']);
    $result = $sql->execute();

    file_put_contents($output_log_dir."/".$log_file, 
        "UPDATE `pdf_uploads` SET `is_paid` = 1 WHERE `order_id` = '$data[order_id]'"
    );
    
    echo json_encode(array('result' => $result));
}

