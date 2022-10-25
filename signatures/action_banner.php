<?php
require_once 'protect.php';

file_put_contents('banner_time', $_POST['banner_time']);

//загружаем картинку
if((!empty($_FILES["banner"])) && ($_FILES['banner']['error'] == 0)) {
    move_uploaded_file($_FILES['banner']['tmp_name'], 'banner.jpg');
}

//загружаем картинку
file_put_contents('banner_link', $_POST['banner_link']);

//загружаем картинку
file_put_contents('banner_text', $_POST['banner_text']);

header('location: index.php');
