<?php

header('Content-Type: application/json');

$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

require_once __DIR__ . '/Src/init.php';
$mysqli = $DbService->getConnection();

$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

// Чтобы хеш не повторялся
do {
    $hash = md5(time() . substr(str_shuffle($permitted_chars), 0, 16));
    $sql = 'SELECT `id` FROM `pdf_uploads` WHERE `hash` = "' . $hash . '"';
    $query = $mysqli->query($sql);
} while ($query->num_rows > 0);

if(!isset($data['filename']) || $data['filename'] == ''){
    throw new Exception('Не удалось найти имя файла');
}

$path = $hash . '/' . $data['filename'];
$FileService->upload($path, base64_decode($data['file']));
if(!$FileService->doesObjectExists($path)){
    throw new Exception("Не удалось создать файл '$path'");
}

$objectInfo = $FileService->headObject($path);
$filesize = $objectInfo['ContentLength'];

unset($data['file']);

$host = 'https://fluid-line.ru';
$file = '/' . $path;

//Проверка на то, что новый заказ уже был оплачен до этого и выставлен ошибочно
$sql = 'SELECT `is_paid` FROM `pdf_uploads` WHERE `order_id` = "' . $data['order_id'] . '" LIMIT 1';
$query = $mysqli->query($sql);
$fetch = $query->fetch_all(MYSQLI_ASSOC);
$is_paid = isset($fetch[0]['is_paid']) ? 1 : 0;
$pay_link = "";

//Формируем ссылку на оплату
if ($data['link']) {
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

$sql = 'INSERT INTO `pdf_uploads` 
                   (`is_paid`, 
                    `path`, 
                    `hash`,
                    `size`,
                    `entity`, 
                    `order_id`, 
                    `pay_block`, 
                    `pay_link`, 
                    `InvoiceId`,
                    `email_hash`,
                    `link`)
	            VALUES 
	               (' . $is_paid . ', 
	            	"' . $host . $file . '", 
	            	"' . $hash . '",
                    "' . $filesize . '",
	            	'  . (isset($data['entity']) && $data['entity'] == true ? 1 : 0) . ', 
	            	"' . $data['order_id'] . '",
	            	"' . (isset($data['pay_block']) && $data['pay_block'] == true ? 1 : 0) . '",
	            	"' . $pay_link . '",
	            	"' . $data['InvoiceId'] . '",
	            	"' . $data['email_hash'] . '",
	            	"' . htmlspecialchars($data['link']) . '")';


$result = $mysqli->query($sql);
$link = $host . '/invoice924' . $hash;

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
Файл загружен: $host$file

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

file_put_contents(__DIR__ . '/statistics-1c.log', $log . PHP_EOL . PHP_EOL, FILE_APPEND);

die();
?>