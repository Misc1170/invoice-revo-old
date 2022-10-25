<?php
require_once 'protect.php';

header('Content-Type: text/html; charset=UTF-8');
require_once 'lib.php';
$db = db_open('contacts.db');

//удаление контакта
if(!empty($_GET['delete_contact']) && $_GET['delete_contact'] != 0){
	//удаляем картинку контакта

    $db->exec('DELETE FROM contacts WHERE id = '.(int)$_GET['delete_contact']);
	header('location: index.php');
}

//добавление контакта
if(!empty($_POST['add_contact'])){
    if(empty($_POST['email']))
        die('поле email должно быть заполнено');

	//загружаем картинку
	if((!empty($_FILES["photo"])) && ($_FILES['photo']['error'] == 0)) {
		$file_name = current(explode('@', $_POST[email]));
		if(!move_uploaded_file($_FILES['photo']['tmp_name'],'images/'.$file_name.'.png'))
			die('Загрузка кратинки не удалась');
	}

    $db->exec('INSERT INTO contacts(`fio`,`fio_en`,`email`,`doljnost`,`doljnost_en`,`phone`,`phone_en`) VALUES("'.escapeString($_POST[fio]).'","'.escapeString($_POST[fio_en]).'","'.escapeString($_POST[email]).'","'.escapeString($_POST[doljnost]).'","'.escapeString($_POST[doljnost_en]).'","'.escapeString($_POST[phone]).'","'.escapeString($_POST[phone_en]).'")');
	header('location: index.php');
}

//сохранение контакта
if(!empty($_POST['save_contact']) && !empty($_POST['contact_id'])){
    if(empty($_POST['email']))
        die('поле email должно быть заполнено');

	//загружаем картинку
	if((!empty($_FILES["photo"])) && ($_FILES['photo']['error'] == 0)) {
		$file_name = current(explode('@', $_POST[email]));
		if(!move_uploaded_file($_FILES['photo']['tmp_name'],'images/'.$file_name.'.png'))
			die('Загрузка кратинки не удалась');
	}

    $db->exec('UPDATE `contacts` SET
	fio = "'.escapeString($_POST[fio]).'",
	fio_en = "'.escapeString($_POST[fio_en]).'",
	email = "'.escapeString($_POST[email]).'",
	doljnost = "'.escapeString($_POST[doljnost]).'",
	doljnost_en = "'.escapeString($_POST[doljnost_en]).'",
	phone = "'.escapeString($_POST[phone]).'",
	phone_en = "'.escapeString($_POST[phone_en]).'"
	WHERE id = '.(int)$_POST['contact_id']);

	header('location: index.php');
}

die('ошибка: неизвестная команда!');

function escapeString($string){
	return str_replace('"','&quot;',$string);
}