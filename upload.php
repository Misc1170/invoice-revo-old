<?php

header('Content-Type: application/json');

$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

$log_file = uniqid(date("Y-m-d-H-i-s") . '_') . '.log';
file_put_contents(__DIR__ . "/logs/uploads/requests/" . $log_file, $postData);

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

// Проверка на то, что новый заказ уже был оплачен до этого и выставлен ошибочно
// $sql = 'SELECT `is_paid` FROM `pdf_uploads` WHERE `order_id` = "' . $data['order_id'] . '" LIMIT 1';
// $query = $mysqli->query($sql);
// $fetch = $query->fetch_all(MYSQLI_ASSOC);
$is_paid = 0;

$pay_link = "";
//Формируем ссылку на оплату
if (isset($data['link'])) {
    $invoiceArray = array();
    $queryString = urldecode(trim(strval($data['link']), '?'));

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
}

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

    $result_query->bind_param("sdddssss",
        $path, $filesize, $entity, $pay_block, $pay_link, $data['email_hash'], $request_link, $hash
    );
} else {
    $result_query = $mysqli->prepare('
        INSERT INTO `pdf_uploads` 
        (`is_paid`, `path`, `hash`, `size`, `entity`, `order_id`, `pay_block`, `pay_link`, `InvoiceId`, `email_hash`, `link`)
            VALUES 
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $result_query->bind_param("dssddsdsdss", 
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
        htmlspecialchars($data['link'])
    );
}

$result = $result_query->execute();

echo json_encode(array(
    'insert-database-result' => $result,
    'link' => $link
));

$datetime = date('d.m.Y H:i:s');
$insert_query_log = $result 
    ? "Добавлено в базу данных: `id` = $mysqli->insert_id" 
    : "Не добавлено в базу данных";

$log = <<<LOG

Отчёт о добавлении счета № $data[InvoiceId] $datetime

Полученные данные:
InvoiceId: $data[InvoiceId]
entity: $data[entity]
pay_block: $data[pay_block]
email_hash: $data[email_hash]
link: $data[link]
filename: $data[filename]

Отчёт о загрузке файла $data[filename]:
Файл загружен: $link

Отчёт о добавлении в базу данных:
Текст запроса
$sql
Результат запроса
$insert_query_log

Ответ сервера
insert-database-result: $result,
link: $link

********************************************************************************************
LOG;

file_put_contents(__DIR__ . "/logs/uploads/results/" . $log_file, $postData . PHP_EOL . PHP_EOL, FILE_APPEND);

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