<?php

header('Content-Type: application/json');

$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

$input_log_dir = __DIR__ . "/logs/uploads/requests/" . date("Y-m-d");
if(!is_dir($input_log_dir)){
    mkdir($input_log_dir, 0755, true);
}
$output_log_dir = __DIR__ . "/logs/uploads/results/" . date("Y-m-d");
if(!is_dir($output_log_dir)){
    mkdir($output_log_dir, 0755, true);
}

$log_file = uniqid(date("H-i-s") . '_') . '.log';
file_put_contents($input_log_dir . '/' . $log_file, $postData);

require_once __DIR__ . '/Src/init.php';
$mysqli = $DbService->getConnection();

if(!isset($data['filename']) || $data['filename'] == ''){
    throw new Exception('Не удалось найти имя файла');
}

//Проверка на существование заказа.
// Раньше при каждой отправке заказа создавалась отдельная запись в базе и файлы. Поэтому для экономии места
// и правильной работы, мы теперь обновляем послеждний добавленные заказ с таким же order_id
$is_order_exists_query = $mysqli->prepare("SELECT * FROM `pdf_uploads` WHERE `order_id` = ? ORDER BY id DESC LIMIT 1");
$is_order_exists_query->bind_param("s", $data['order_id']);
$is_order_exists_query->execute();

$existing_order = $is_order_exists_query->get_result()->fetch_assoc();

// Если уже есть заказ, то сначала переиспользуем его хеш, либо генерируем новый
if($existing_order !== null){
    $hash = $existing_order['hash'];
} else {
    $hash = createUniqueHash($mysqli);
}

$path = $hash . '/' . $data['filename'];
$link = 'https://fluid-line.ru/invoice924' . $hash;

// Удяляем прошлый архив к заказу, если у них разное имя (так как новый файл не перезапишет старый)
if($existing_order !== null && $path !== $existing_order['path']){
    $FileService->delete($existing_order['path']);
}

$FileService->upload($path, base64_decode($data['file']));
if(!$FileService->doesObjectExists($path)){
    throw new Exception("Не удалось создать файл '$path'");
}

unset($data['file']);

$filesize = $FileService->headObject($path)['ContentLength'];

$pay_link = "";
//Формируем ссылку на оплату
if (isset($data['link']) && $data['link'] != '') {
    $pay_link = generatePaylink($data['link']);
}

$is_paid = 0;
$entity = isset($data['entity']) && $data['entity'] == true ? 1 : 0;
$pay_block = isset($data['pay_block']) && $data['pay_block'] !== '' ? $data['pay_block'] : 0;
$request_link = htmlspecialchars($data['link']);

// Если уже есть заказ, то обновляем его, либо инсертим
if($existing_order !== null){
    $result_query = $mysqli->prepare('
        UPDATE `pdf_uploads` 
        SET path = ?, `size` = ?, `entity` = ?, `pay_block` = ?, `pay_link` = ?, `email_hash` = ?, `link` = ?
        WHERE `hash` = ?
    ');

    $result_query->bind_param("siiissss",
        $path, $filesize, $entity, $pay_block, $pay_link, $data['email_hash'], $request_link, $hash
    );

    file_put_contents($output_log_dir."/".$log_file, "
        UPDATE `pdf_uploads` 
        SET path = '$path', `size` = $filesize, `entity` = $entity, `pay_block` = $pay_block, `pay_link` = '$pay_link', `email_hash` = '$data[email_hash]', `link` = '$request_link'
        WHERE `hash` = '$hash'
    ");


} else {
    $result_query = $mysqli->prepare('
        INSERT INTO `pdf_uploads` 
        (`is_paid`, `path`, `hash`, `size`, `entity`, `order_id`, `pay_block`, `pay_link`, `InvoiceId`, `email_hash`, `link`)
            VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $db_save_link = htmlspecialchars($data['link']);
    $result_query->bind_param("issiisisiss", 
        $is_paid, 
        $path, 
        $hash,
        $filesize,
        $entity,
        $data['order_id'],
        $pay_block,
        $pay_link, 
        $data['InvoiceId'],
        $data['email_hash'],
        $request_link
    );

    file_put_contents($output_log_dir."/".$log_file, "
        INSERT INTO `pdf_uploads` 
        (`is_paid`, `path`, `hash`, `size`, `entity`, `order_id`, `pay_block`, `pay_link`, `InvoiceId`, `email_hash`, `link`)
            VALUES 
        ($is_paid, '$path', '$hash', $filesize, $entity, $data[order_id], $pay_block, '$pay_link', $data[InvoiceId], $data[email_hash], $db_save_link)
    ");
}

$result = $result_query->execute();

echo json_encode(array(
    'insert-database-result' => $result,
    'link' => $link
));

function createUniqueHash($mysqli){
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    // Чтобы хеш не повторялся
    do {
        $hash = md5(time() . substr(str_shuffle($permitted_chars), 0, 16));
        $sql = 'SELECT `id` FROM `pdf_uploads` WHERE `hash` = "' . $hash . '"';
        $query = $mysqli->query($sql);
    } while ($query->num_rows > 0);

    return $hash;
}

function generatePaylink($src_link){

    $pay_link = false;
    $invoiceArray = array();
    $queryString = urldecode(trim(strval($src_link), '?'));

    $fetchPay = explode('&', $queryString);
    foreach ($fetchPay as $rowInner) {
        $exp = explode('=', $rowInner);
        $invoiceArray[lcfirst($exp[0])] = $exp[1];
    }

    foreach ($invoiceArray as $k => $v)
        $$k = $v;

    do {
        include __DIR__ . '/oplata2.php';
        $pay_link = $linka;
    } while (!$pay_link);

    return $pay_link;
}