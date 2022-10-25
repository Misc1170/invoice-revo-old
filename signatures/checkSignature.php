<?php
require_once 'protect.php';

header('Content-Type: text/html; charset=UTF-8');
require_once 'lib.php';
$db = db_open('contacts.db');

if(empty($_GET['email']))
	die('ОШИБКА. Укажите email, например ?email=user@email.ru&type=banner');
	
$result = $db->query('SELECT * FROM `contacts` WHERE `email` = "'.$_GET['email'].'"');
$row = $result->fetch(PDO::FETCH_ASSOC);
if(empty($row))
	die('0');
else
	die('1');