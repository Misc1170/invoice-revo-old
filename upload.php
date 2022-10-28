<?php
header('Content-Type: application/json');

$postData = file_get_contents('php://input');
$data = json_decode($postData, true);

$config = require_once __DIR__ . '/config.php';
$db_config = $config['databases']['main'];

$mysqli = new mysqli(
    $db_config['host'] . ':' . $db_config['port'],
    $db_config['user'], 
    $db_config['password'], 
    $db_config['db']
);
$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

// Чтобы хеш не повторялся
do {
    $hash = md5(time() . substr(str_shuffle($permitted_chars), 0, 16));
    $sql = 'SELECT `id` FROM `pdf_uploads` WHERE `hash` = "' . $hash . '"';
    $query = $mysqli->query($sql);
} while ($query->num_rows > 0);

$path = 'upload-pdfs/files/' . $hash . '/';

if (!is_dir($path))
    mkdir($path, 0777, true);

$path .= $data['filename'];
$result = file_put_contents($path, base64_decode($data['file']));

$upload_success = file_exists($path);

$host = 'https://fluid-line.ru';
$file = '/' . $path;

//Проверка на то, что новый заказ уже был оплачен до этого и выставлен ошибочно
$sql = 'SELECT `is_paid` FROM `pdf_uploads` WHERE `order_id` = "' . $data['order_id'] . '" LIMIT 1';
$query = $mysqli->query($sql);
$fetch = $query->fetch_all(MYSQLI_ASSOC);
$is_paid = isset($fetch[0]['is_paid']) ? $fetch[0]['is_paid'] : 0;
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


$data_to_string = $data;
unset($data_to_string['file']);

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
	               ("' . $is_paid . '", 
	            	"' . $host . $file . '", 
	            	"' . $hash . '", 
	            	' . @filesize($path) . ', 
	            	' . ($data['entity'] ? 1 : 0) . ', 
	            	"' . $data['order_id'] . '",
	            	"' . $data['pay_block'] . '",
	            	"' . $pay_link . '",
	            	"' . $data['InvoiceId'] . '",
	            	"' . $data['email_hash'] . '",
	            	"' . htmlspecialchars($data['link']) . '")';

if ($upload_success)
    $result = $mysqli->query($sql);


echo json_encode(array(
    'upload_file-result' => $upload_success,
    'insert-database-result' => $result,
    'link' => $result && $upload_success ? $host . '/invoice924' . $hash : ""
));


$datetime = date('d.m.Y H:i:s');

$upload_log = $upload_success ? "загружен: $host$file" : "не загружен";
$insert_query_log = $result ? "Добавлено в базу данных: `id` = $mysqli->insert_id" : "Не добавлено в базу данных";
$link_log = $result && $upload_success ? $host . '/invoice924' . $hash : "";

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
Файл $upload_log

Отчёт о добавлении в базу данных:
Текст запроса
$sql
Результат запроса
$insert_query_log

Ответ сервера
upload_file-result: $upload_success,
insert-database-result: $result,
link: $link_log

********************************************************************************************
LOG;

file_put_contents(__DIR__ . '/statistics-1c.log', $log . PHP_EOL . PHP_EOL, FILE_APPEND);

die();
?>