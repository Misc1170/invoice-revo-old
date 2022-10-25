<?php
require_once 'protect.php';

header('Content-Type: text/html; charset=UTF-8');
require_once 'lib.php';
$db = db_open('contacts.db');

if(empty($_GET['email']))
	die('ОШИБКА. Укажите email, например ?email=user@email.ru&type=banner');
	
//подпись по умолчанию - с баннером
if(empty($_GET['type']))
	$_GET['type'] = 'banner';
	
//проверяем актуальность баннера, меняем подпись на main ("без баннера")
if( checkBanner() == false && $_GET['type'] == 'banner')
    $_GET['type'] = 'main';
	
$template = getTemplate();
echo parseTemplate($db, $template);

///////////////////////////////

function getTemplate(){
	if(!file_exists('template_'.$_GET[type].'.html'))
		die('ОШИБКА: шаблон подписи не найден: '.'template_'.$_GET[type].'.html');
	return file_get_contents('template_'.$_GET[type].'.html');
}

function parseTemplate($db, $template){
	$result = $db->query('SELECT * FROM `contacts` WHERE `email` = "'.$_GET['email'].'"');
	$row = $result->fetch(PDO::FETCH_ASSOC);
	if(empty($row))
		die('ОШИБКА: пользователь не найден');

    $row['img'] = current(explode('@', $_GET['email'])).'.png';
    $row['banner_link'] = file_get_contents('banner_link');
    $row['banner_text'] = file_get_contents('banner_text');

	return str_replace(
		array('[[fio]]','[[fio_en]]','[[email]]','[[doljnost]]','[[doljnost_en]]', '[[phone]]','[[phone_en]]','[[img]]','[[time]]','[[banner_link]]','[[banner_text]]'),
		array($row['fio'],$row['fio_en'],$row['email'],$row['doljnost'],$row['doljnost_en'],$row['phone'],$row['phone_en'],$row['img'],time(),$row['banner_link'],$row['banner_text']),
		$template
	);
}

function checkBanner(){
	$dateString = file_get_contents('banner_time');
	
	if(empty($dateString))
		return false;
		
	$dateDigital = strtotime($dateString);
	
	if(time() > $dateDigital)
		return false;
		
	return true;
}