<?php
require_once 'protect.php';

file_put_contents('banner_time1', $_POST['banner_time1']);

//загружаем картинку
if((!empty($_FILES["banner1"])) && ($_FILES['banner1']['error'] == 0)) {
    move_uploaded_file($_FILES['banner1']['tmp_name'], 'banner1.jpg');
}

//загружаем картинку
file_put_contents('banner_link1', $_POST['banner_link1']);

//загружаем картинку
file_put_contents('banner_text1', $_POST['banner_text1']);

header('location: index.php');
