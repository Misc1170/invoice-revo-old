<?php

if(!isset($_GET['q'])){
    return;
}

function is_IE()
{
    if(!isset($_SERVER['HTTP_USER_AGENT'])){
        return 0;
    }
    
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false ||
        strpos($_SERVER['HTTP_USER_AGENT'], 'rv:11.0') !== false) {
        return 1;
    } else
        return 0;
}

function getFileSize($size)
{
    $size = $size / 1024 / 1024;
    return $size < 1 ? round($size * 1024, 2) . ' Кб' : round($size, 2) . ' Мб';
}

function deleteUnzipped($path)
{
    if (!is_dir($path)){
        return false;
    }

    $files = array_slice(scandir($path), 2);
    if (empty($files)){
        return false;
    }

    foreach ($files as $file) {
        if (file_exists($path . '/' . $file)){
            unlink($path . '/' . $file);
        }
    }
    return true;
}

function unzipInvoice($zipFile, $extract2)
{
    $extract2 = rtrim($extract2, '\\/');
    deleteUnzipped($extract2);

    $zip = new ZipArchive();
    $zip_status = file_exists($zipFile) ? $zip->open($zipFile) : false;

    if ($zip_status === true) {
        $pswd = $_GET['pswd'];

        if ($zip->setPassword(trim($pswd))) {
            if (!$zip->extractTo($extract2)){
                throw new \Exception('Не удалось распаковать документы к заказу');
            }
        }

        $zip->close();
    } else {
        return false;
    }

    return true;
}

require_once __DIR__ . '/Src/init.php';
$mysqli = $DbService->getConnection();

$hash = trim(str_replace('invoice924', '', $_GET['q']), '/');

$sql = 'SELECT * FROM `pdf_uploads` WHERE `hash` = "' . $hash . '" LIMIT 1';
$query = $mysqli->query($sql);
$fetch = current($query->fetch_all(MYSQLI_ASSOC));

if(!$fetch){
    echo 'Не удалось найти ваш заказ';
    return;
}

$sql = 'UPDATE `pdf_uploads` SET `lastAction` = CURRENT_TIMESTAMP, `views` = `views` + 1 WHERE `id` = ' . $fetch['id'];
$query = $mysqli->query($sql);

if (strtotime($fetch['visited']) < 0) {
    $sql = 'UPDATE `pdf_uploads` SET 
                `visited` = CURRENT_TIMESTAMP,
                `client_id` = "' . trim($_GET['client_id']) . '", 
                `client_code` = "' . trim($_GET['client_code']) . '" 
            WHERE `id` = ' . $fetch['id'];
    $query = $mysqli->query($sql);
}

$zipArciveName = basename($fetch['path']);
$fileSize = getFileSize($fetch['size']);

$invoiceStorageDir = __DIR__ . '/upload-pdfs/files/' . trim($fetch['hash']);
if(!is_dir($invoiceStorageDir)){
    mkdir($invoiceStorageDir, 0755, true);
}

$zipFile = $invoiceStorageDir . '/' . basename($fetch['path']);

//Скачиваем файл из облака
$FileService->downloadAs($fetch['path'], $zipFile);

//Распаковка
$extract2 = $invoiceStorageDir . '/unzipped';
$unzippedSuccessfully = unzipInvoice($zipFile, $extract2);

$pdfInvoiceFileUrl = false;
$excelInvoiceFileUrl = false;
if ($unzippedSuccessfully){
    $mysqli->query('UPDATE `pdf_uploads` SET `unzip` = 1 WHERE `id` = ' . $fetch['id']);

    $baseUrl = sprintf(
        '%s://%s/upload-pdfs/files/%s/unzipped',
        ($_SERVER['HTTPS'] ? 'https' : 'http'),
        $_SERVER['HTTP_HOST'],
        trim($fetch['hash'])
    );
    $pdfInvoiceFileUrl = $baseUrl . '/' . str_replace('zip', 'pdf', $zipArciveName);
    $excelInvoiceFileUrl = $baseUrl . '/' . str_replace('zip', 'xlsx', $zipArciveName);
}


$linka = $fetch['pay_link'];

if (!$fetch['entity']) {
    $a_inner = 'href="' . $linka . '" target="_blank"';
    $pay_class = 'wow animated bounce infinite';
} else {
    $a_inner = 'onclick="showAlert()"';
    $pay_class = 'disabled';
}

if ($fetch['is_paid'] || $fetch['pay_block']) {
    $a_inner = 'style="opacity: .3; cursor: default;" title="Оплата по карте недоступна"';
    $pay_class = '';
}


//Шапка
include_once 'elements/header.php';

//вывод таблицы
include_once 'elements/table.php';

//вывод превьюшек pdf
include_once 'elements/pdf-js.php';

//реализованные проекты
include_once 'elements/cases.php';

//Комментарии
include_once 'elements/feedback.php';

//Подвал
include_once 'elements/footer.php';