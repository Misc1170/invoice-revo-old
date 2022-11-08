<?php


function curl_get_content($url)
{
    $url = urldecode($url);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 0);

    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
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
    if (!is_dir($path))
        return false;
    $files = array_slice(scandir($path), 2);
    if (empty($files))
        return false;
    foreach ($files as $file) {
        if (file_exists($path . '/' . $file))
            unlink($path . '/' . $file);
    }
    return true;
}

function unzip($zipFile, $extract2, $path)
{

    session_start();
    deleteUnzipped($extract2);
    $zip = new ZipArchive();

    $zip_status = file_exists($zipFile) ? $zip->open($zipFile) : false;

    if ($zip_status === true) {
        $pswd = $_GET['pswd'];
        if ($zip->setPassword(trim($pswd))) {
            if (!$zip->extractTo($extract2))
                $_SESSION['error-password'] = 1;
        }

        $zip->close();
    } else {
        $_SESSION['error-password'] = 1;
        return false;
    }

    $pdfFile = current(array_slice(scandir($extract2), 2));
    $path_zip = explode('/', $path);
    array_pop($path_zip);
    return implode('/', $path_zip) . '/unzipped/' . $pdfFile;
}


function get_current_url()
{
    return 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

if(!isset($_GET['q'])){
    return;
}

$config = require_once __DIR__ . '/config.php';
$db_config = $config['databases']['main'];

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli = new mysqli(
    $db_config['host'] . ':' . $db_config['port'],
    $db_config['user'], 
    $db_config['password'], 
    $db_config['db']
);

$hash = trim(str_replace('invoice924', '', $_GET['q']), '/');

$sql = 'SELECT * FROM `pdf_uploads` WHERE `hash` = "' . $hash . '" LIMIT 1';
$query = $mysqli->query($sql);
$fetch = current($query->fetch_all(MYSQLI_ASSOC));

if(!$fetch){
    echo 'Не удалось найти ваш заказ';
    return;
}

$sql = 'UPDATE `pdf_uploads` SET 
            `lastAction` = CURRENT_TIMESTAMP,
            `views` = `views` + 1 
        WHERE `id` = ' . $fetch['id'];
$query = $mysqli->query($sql);


if (strtotime($fetch['visited']) < 0) {
    $sql = 'UPDATE `pdf_uploads` SET 
                `visited` = CURRENT_TIMESTAMP,
                `client_id` = "' . trim($_GET['client_id']) . '", 
                `client_code` = "' . trim($_GET['client_code']) . '" 
            WHERE `id` = ' . $fetch['id'];
    $query = $mysqli->query($sql);
}

$fetch['path'] = str_replace(
    'https://fluid-line.ru',
    'http://' . $_SERVER['HTTP_HOST'] .'/',
    $fetch['path']
);

$explode = explode('/', $fetch['path']);
$fileName = end($explode);
$fileSize = getFileSize($fetch['size']);

$zipFile = str_replace(
    array('http://' . $_SERVER['HTTP_HOST'], 'https://fluid-line.ru'),
    $_SERVER['DOCUMENT_ROOT'],
    $fetch['path']
);

$pathArr = explode('/', $zipFile);
array_pop($pathArr);
$extract2 = implode('/', $pathArr) . '/unzipped/';

//Распаковка
$pdfPath = unzip($zipFile, $extract2, $fetch['path']);
if ($pdfPath)
    $mysqli->query('UPDATE `pdf_uploads` SET `unzip` = 1 WHERE `id` = ' . $fetch['id']);

if (isset($_GET['open-invoice'])) {
    $pdfPath = $hash . end(explode($hash, $pdfPath));
    echo <<<SCRIPT
<script>
    fetch('delete-file.php', {
            method: 'POST',
            body: JSON.stringify({
                file: '$pdfPath'
            }),
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (response) {
                console.log(response);
            });
    window.location.href = 'https://fluid-line.ru/invoice/invoice.php?pdf_link=$pdfPath';
</script>
SCRIPT;
    exit();
}

$pdfFile = end(explode('/', $pdfPath));

/**
 * @var $linka string
 */
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

