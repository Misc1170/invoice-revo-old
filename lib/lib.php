<?php

use Google\Cloud\BigQuery\BigQueryClient;

class Fetch
{
    private $config = array(
        'host' => "mysql99.1gb.ru",
        'username' => 'gb_jino_revo',
        'password' => '5dfa9bb68wps',
        'db_name' => 'gb_jino_revo'
    );

    public function __construct($data = array())
    {
        $method = $data['method'] ? strval($data['method']) : 'init';

        if ($data['config'])
            $this->config = $data['config'];

        if (!method_exists($this, $method)) {
            json(array('result' => false, 'message' => 'Запрашиваемый метод отсутствует', 'data' => $data));
            return false;
        }
        
        require_once __DIR__ . '/database.class.php';
        database::getInstance()->Connect($this->config['username'], $this->config['password'], $this->config['db_name'], $this->config['host']);
        $this->$method($data);
        
        return true;
    }

    protected function check_existence($tablename, $data)
    {
        if (!is_array($data) || empty($data))
            return false;

        $columns = $this->get_table_fields($tablename);

        $sql = 'SELECT * FROM `' . $tablename . '` WHERE ';

        foreach ($data as $field => $value) {
            if (in_array($field, $columns))
                $sql .= '`' . $field . '` = "' . str_replace('"', '\"', $value) . '" ' . ($value != end($data) ? ' AND ' : '');
        }
        $sql .= ' LIMIT 1';
        $fetch = database::getInstance()->Select($sql);
        return !empty($fetch);
    }

    protected function get_table_fields($table_name)
    {
        $columns = database::getInstance()->Select(' SHOW COLUMNS FROM `' . $table_name . '`');
        return fetch_to_array($columns, 'Field');
    }

    protected function insert($table_name, $data, $multiple_insert = false)
    {
        if (!$table_name || !is_array($data))
            return false;

        $columns = $this->get_table_fields($table_name);
        $fields = $values = '';
        if (!$multiple_insert) {
            $values .= '(';
            $data = is_array($data[0]) ? $data[0] : $data;
            foreach ($data as $field => $value) {
                if (in_array($field, $columns)) {
                    $fields .= '`' . $field . '`,';
                    $values .= '"' . str_replace('"', '\"', $value) . '",';
                }
            }
            $values = trim($values, ',') . '),';
        } else {
            foreach ($data as $i => $row) {
                if (!is_array($row))
                    continue;
                $values .= '(';
                foreach ($row as $field => $value) {
                    if (in_array($field, $columns)) {
                        if ($i == 0)
                            $fields .= '`' . $field . '`,';
                        $values .= '"' . $value . '",';
                    }
                }
                $values = trim($values, ',') . '),';
            }
        }
        $sql = 'INSERT INTO `' . $table_name . '` (' . trim($fields, ',') . ') VALUES ' . trim($values, ',');
        return database::getInstance()->QueryInsert($sql);
    }

    protected function update($table_name, $data, $identifier_value, $identifier = 'id')
    {
        if (!$table_name || !is_array($data) || empty($data) || !$identifier_value)
            return false;

        $columns = $this->get_table_fields($table_name);

        if (!in_array($identifier, $columns))
            return false;

        $sql = 'UPDATE `' . $table_name . '` SET ';

        foreach ($data as $field => $value) {
            if (in_array($field, $columns))
                $sql .= '`' . $field . '` = "' . str_replace('"', '\"', $value) . '",';
        }

        $sql = trim($sql, ',');

        $sql .= ' WHERE `' . $identifier . '` = "' . $identifier_value . '"';

        $result = database::getInstance()->Query($sql);

        return $result ?
            database::getInstance()
                ->Select('SELECT `' . implode('`, `', array_intersect(array_keys($data), $columns)) . '` FROM `' . $table_name . '` 
                                WHERE `' . $identifier . '` = "' . $identifier_value . '"') : $result;
    }

    protected function delete($table_name, $identifier_value, $identifier = 'id')
    {
        if (!$table_name || !$identifier_value)
            return false;

        $columns = $this->get_table_fields($table_name);

        if (!in_array($identifier, $columns))
            return false;

        $sql = 'DELETE FROM `' . $table_name . '` WHERE `' . $identifier . '` = "' . $identifier_value . '"';
        return database::getInstance()->Query($sql);
    }


    public function getter($tablename, $data = array(), $fields = '*')
    {
        if (!$tablename || !is_array($data))
            return false;

        fields:
        if (is_array($fields))
            $fields = '`' . implode('`, `', $fields) . '`';
        elseif (strval($fields) && $fields != '*') {
            $fields = explode(',', $fields);
            goto fields;
        }

        $columns = $this->get_table_fields($tablename);

        $where = '';

        if (!empty($data)) {
            $where .= ' WHERE ';
            foreach ($data as $field => $value) {
                if (in_array($field, $columns))
                    $where .= '`' . $field . '` = "' . $value . '"' . ($field != end(array_keys($data)) ? ' AND ' : '');
            }
        }
        $sql = 'SELECT ' . $fields . ' FROM `' . $tablename . '`' . $where;
        return database::getInstance()->Select($sql);
    }
}

//Emails для рассылки писем
$emails_to_send_letters = array(
    'yury' => 'titov_yw@mail.ru',
    'alex' => 'alex@fluid-line.ru',
    'avp' => 'avp@fluid-line.ru',
    'managers' => 'mail@fluid-line.ru'
);

function get_some_array_fields($array, $fields)
{
    $fields = is_array($fields) ? $fields : array($fields);
    $result = array();
    foreach ($array as $key => $value) {
        if (in_array($key, $fields))
            $result[$key] = $value;
    }
    return $result;
}

function fetch_init($className)
{
    $className = strtoupper($className);
    if (!empty($_FILES)) {
        if (method_exists($className, 'upload_files'))
            $className::upload_files($_FILES);
        return true;
    }
    $ajax_data = file_get_contents('php://input');
    if (!$ajax_data)
        return false;
    return new $className(json_decode($ajax_data, 1));
}

function json($data)
{
    echo json_encode($data);
}

function fetch_to_array($fetch, $field = false)
{
    $result = array();
    if (!in_array($field, array_keys(current($fetch))))
        $field = false;
    foreach ($fetch as $row)
        $result[] = $field ? $row[$field] : current($row);
    return $result;
}

function date_rus_format($date, $time = false)
{
    $date = date_parse($date);
    $months = array(1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
    return $date['day'] . ' ' . $months[$date['month']] . ' ' . $date['year'] . ' г.' .
        ($time ? ' в ' . $date['hour'] . ':' . (intval($date['minute']) < 10 ? '0' . $date['minute'] : $date['minute']) : '');
}

function current_ending($count, $endings = array())
{
    switch (1) {
        case ($count % 10 == 1 && $count % 100 != 11):
            $result = $endings[0];
            break;
        case (in_array($count % 10, array(2, 3, 4)) && !in_array($count % 100, array(12, 13, 14))):
            $result = $endings[1];
            break;
        default:
            $result = $endings[2];
    }
    return $result;
}

if (!function_exists('translit')) {
    function translit($string)
    {
        $abc = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z',
            'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
            'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
            'ъ' => '\'', 'ы' => 'y', 'ь' => '\'', 'э' => 'e', 'ю' => 'ju', 'я' => 'ja'
        );

        $translit = '';
        $string = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($string as $char) {
            if (preg_match('/\p{Cyrillic}/ui', $char))
                $translit .= $abc[mb_strtolower($char)];
            else
                $translit .= mb_strtolower($char);
        }

        $translit = preg_replace('/\s+/', '-', $translit);
        $translit = preg_replace('/(–|-){2,}/', '-', $translit);
        return preg_replace('/[^a-z\d+\-\']/', "", trim($translit, '-'));
    }
}

function translit_string($string)
{
    $abc = array(
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'j', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ъ' => '\'', 'ы' => 'y', 'ь' => '\'', 'э' => 'e', 'ю' => 'ju', 'я' => 'ja',
        'А' => 'a', 'Б' => 'b', 'В' => 'v', 'Г' => 'g', 'Д' => 'd', 'Е' => 'e', 'Ё' => 'e', 'Ж' => 'j', 'З' => 'z',
        'И' => 'i', 'Й' => 'y', 'К' => 'k', 'Л' => 'l', 'М' => 'm', 'Н' => 'n', 'О' => 'o', 'П' => 'p', 'Р' => 'r',
        'С' => 's', 'Т' => 't', 'У' => 'u', 'Ф' => 'f', 'Х' => 'h', 'Ц' => 'c', 'Ч' => 'ch', 'Ш' => 'sh', 'Щ' => 'sch',
        'Ъ' => '\'', 'Ы' => 'y', 'Ь' => '\'', 'Э' => 'e', 'Ю' => 'ju', 'Я' => 'ja'
    );

    $translit = '';
    $string = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);

    foreach ($string as $char) {
        if (preg_match('/\p{Cyrillic}/ui', $char))
            $translit .= $abc[$char];
        else
            $translit .= $char;
    }

    return preg_replace('/\s/', '-', strtolower(trim($translit)));
}

function textCutter($string, $char_limit = 15)
{
    $string = trim(strip_tags($string));
    return
        mb_strwidth($string, 'utf-8') > $char_limit + 10 ?
            mb_substr($string, 0, $char_limit, "utf-8") . '...'
            : $string;
}


function mailSender($to, $subject, $message)
{
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: mail@fluid-line.ru \r\n";
    return mail($to, $subject, $message, $headers);
}

function getAge($birthday)
{
    $age = floor((time() - strtotime($birthday)) / (3600 * 24 * 365));
    return $age . ' ' . current_ending($age, array('год', 'года', 'лет'));
}

function getPeriod($date)
{
    $period = (time() - strtotime($date)) / (3600 * 24);

    switch (1) {
        case $period < 1:
            return ' менее суток';
            break;
        case ($period / intval(date('t')) < 1):
            return ' менее месяца';
            break;
        case ($period / 365 < 1):
            return ceil($period / 30) . ' ' . current_ending(ceil($period / 30), array('месяц', 'месяца', 'месяцев'));
            break;
        case ($period / 365 > 1):
            $years = floor($period / 365) . ' ' . current_ending(floor($period / 365), array('год', 'года', 'лет'));
            $monthes = floor($period % 365 / 30) > 0 ?
                ' и ' . floor($period % 365 / 30) . ' ' . current_ending(ceil($period % 365 / 30), array('месяц', 'месяца', 'месяцев')) : '';
            return $years . $monthes;
            break;
    }

    return 'неопределенное время';
}

function get_date($timestamp)
{
    return current(explode(' ', $timestamp));
}


function curl_get_content($url, $cookie = false)
{
    $url = urldecode($url);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    if ($cookie)
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 0);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;

}

if (!function_exists('curl')) {
    function curl($url, $query = array(), $headers = array())
    {
        $ch = curl_init();
        curl_setopt_array($ch,
            array(
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $query
            ));
        $response = curl_exec($ch);
        curl_close($ch);
        return is_array(json_decode($response, 1)) ? json_decode($response, 1) : $response;
    }
}

function textWrapper($text, $char_limit = 100)
{
    return
        mb_strwidth($text, 'utf-8') > $char_limit + 30 ? //Если длинна текста превышает заданный лимит более чем на 30 знаков
            mb_substr($text, 0, $char_limit, "utf-8")
            . '<span class="hidden">' . mb_substr($text, $char_limit, mb_strwidth($text, 'utf-8') - $char_limit, 'utf-8') . '</span>'
            . '<span class="details"> <span class="inner white-space-nowrap" onclick="textLimit(this)"><span class="inner-text">Развернуть</span> <i class="fa fa-caret-down" aria-hidden="true"></i></span></span>'
            : $text;
}

function mailer($subject, $message, $to = "titov_yw@mail.ru")
{
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From:mail@fluid-line.ru \r\n";

    return mail($to, $subject, $message, $headers);
}


function upload_files($files, $upload_dir, $rename = 1)
{
    $uploaded = array();

    for ($i = 0; $i < count($files['file']['tmp_name']); $i++) {
        $fileName = $rename ? time() . rand(0, 10000) . time() . '.jpg' : $files['file']['name'][$i];
        move_uploaded_file($files['file']['tmp_name'][$i], $upload_dir . '/' . $fileName);
        array_push($uploaded, str_replace($_SERVER['DOCUMENT_ROOT'], '', $upload_dir . '/' . $fileName));
    }

    return $uploaded;
}

function get_current_url()
{
    return 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

function get_ob_content($file, $data = array())
{

    if (!is_array($data) || !file_exists($file))
        return false;

    foreach ($data as $k => $v)
        $$k = $v;

    ob_start();
    include $file;
    $html = ob_get_contents();
    ob_clean();

    return $html;
}

function get_hash_string($lenght)
{
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    return substr(str_shuffle($permitted_chars), 0, $lenght);
}

function delete_some_fetch_fields($fetch, $fields = array('id'))
{
    if (empty($fields))
        return $fetch;

    foreach ($fetch as $i => $row) {
        foreach ($fields as $field)
            unset($fetch[$i][$field]);
    }

    return $fetch;
}

function get_filesize($file)
{
    $sitename = 'http' . ($_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
    $file = $_SERVER['DOCUMENT_ROOT'] . str_replace($sitename, '', $file);

    if (file_exists($file))
        $filesize = filesize($file) / 1024 / 1024;
    else
        return '0 Кб';

    return $filesize < 1 ? round($filesize * 1024, 2) . ' Кб' : round($filesize, 2) . ' Мб';
}


function get_another_date($date, $offset, $format = 'Y-m-d')
{
    return date($format, strtotime($offset, strtotime($date)));
}


function get_path($dir)
{
    return str_replace($_SERVER['DOCUMENT_ROOT'], '', $dir);
}


function normJsonStr($str)
{
    $str = preg_replace_callback('/\\\\u([a-f0-9]{4})/i', create_function('$m', 'return chr(hexdec($m[1])-1072+224);'), $str);
    return iconv('cp1251', 'utf-8', $str);
}


function GET($url, $headers)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return is_array(json_decode($response, 1)) ? json_decode($response, 1) : $response;
}

function POST($url, $query = array(), $headers = array("content-type: application/json"))
{
    $ch = curl_init();
    curl_setopt_array($ch,
        array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $query
        ));
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err)
        return array('error' => "cURL Error #:" . $err);

    return is_array(json_decode($response, 1)) ? json_decode($response, 1) : $response;
}


function access()
{
    if (!in_array($_SERVER['REMOTE_ADDR'], $GLOBALS['allowed_ip']))
        header('Location: /');
}

function ip()
{
    if (!in_array($_SERVER['REMOTE_ADDR'], $GLOBALS['allowed_ip']))
        exit('Доступ запрещен!!!');
}

$email_reg_exp = '/[a-z?A-Z?\d?\-?\._\+]+@[a-z?A-Z?\d?\-?\._]+\.[a-z]+/';

function json_fix_cyr($json_str)
{
    if (!$json_str)
        return '';

    else if (mb_detect_encoding($json_str) == "ASCII") {
        $res = json_decode('{"key":"' . $json_str . '"}', 1);
        return mb_convert_encoding($res['key'], 'UTF-8');
    }

    $cyr_chars = array(
        '\u0430' => 'а', '\u0410' => 'А',
        '\u0431' => 'б', '\u0411' => 'Б',
        '\u0432' => 'в', '\u0412' => 'В',
        '\u0433' => 'г', '\u0413' => 'Г',
        '\u0434' => 'д', '\u0414' => 'Д',
        '\u0435' => 'е', '\u0415' => 'Е',
        '\u0451' => 'ё', '\u0401' => 'Ё',
        '\u0436' => 'ж', '\u0416' => 'Ж',
        '\u0437' => 'з', '\u0417' => 'З',
        '\u0438' => 'и', '\u0418' => 'И',
        '\u0439' => 'й', '\u0419' => 'Й',
        '\u043a' => 'к', '\u041a' => 'К',
        '\u043b' => 'л', '\u041b' => 'Л',
        '\u043c' => 'м', '\u041c' => 'М',
        '\u043d' => 'н', '\u041d' => 'Н',
        '\u043e' => 'о', '\u041e' => 'О',
        '\u043f' => 'п', '\u041f' => 'П',
        '\u0440' => 'р', '\u0420' => 'Р',
        '\u0441' => 'с', '\u0421' => 'С',
        '\u0442' => 'т', '\u0422' => 'Т',
        '\u0443' => 'у', '\u0423' => 'У',
        '\u0444' => 'ф', '\u0424' => 'Ф',
        '\u0445' => 'х', '\u0425' => 'Х',
        '\u0446' => 'ц', '\u0426' => 'Ц',
        '\u0447' => 'ч', '\u0427' => 'Ч',
        '\u0448' => 'ш', '\u0428' => 'Ш',
        '\u0449' => 'щ', '\u0429' => 'Щ',
        '\u044a' => 'ъ', '\u042a' => 'Ъ',
        '\u044b' => 'ы', '\u042b' => 'Ы',
        '\u044c' => 'ь', '\u042c' => 'Ь',
        '\u044d' => 'э', '\u042d' => 'Э',
        '\u044e' => 'ю', '\u042e' => 'Ю',
        '\u044f' => 'я', '\u042f' => 'Я',

        '\r' => '',
        '\n' => '<br />',
        '\t' => ''
    );

    foreach ($cyr_chars as $cyr_char_key => $cyr_char) {
        $json_str = str_replace($cyr_char_key, $cyr_char, $json_str);
    }
    return $json_str;
}

$GLOBALS['allowed_ip'] = array(
    '94.159.54.30',
    '62.105.33.210',
    '62.117.119.177',
    '62.117.119.176',
    '62.117.119.142',
    '185.212.88.178',
    '77.108.67.26',
    '62.105.43.74',
    '62.105.43.75',
    '62.105.43.76',
    '62.105.43.77',
    '62.105.43.78',
    '127.0.0.1',
    '192.168.0.197',
    '192.168.0.156',
    '192.168.0.185',
    '178.140.155.146',
    '77.108.67.7',
    '77.108.67.8',
    '77.108.67.9',
    '213.24.127.125'
);