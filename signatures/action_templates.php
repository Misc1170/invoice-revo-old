<?php
require_once 'protect.php';

if(!empty($_POST['template_main']))
	file_put_contents('template_main.html', $_POST['template_main']);

if(!empty($_POST['template_banner']))
	file_put_contents('template_banner.html', $_POST['template_banner']);

if(!empty($_POST['template_en']))
	file_put_contents('template_en.html', $_POST['template_en']);
	
header('location: index.php');
	