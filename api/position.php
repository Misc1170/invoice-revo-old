<?php

require_once __DIR__ . '/../Src/init.php';

$post = file_get_contents('php://input');
if ($post == ''){
    return;
}
$post = json_decode($post, 1);

if(intval($post['id'])){

	header('Content-Type: text/html; charset=utf-8');

	$position = strval(strip_tags($post['position']));

	//Подключаемся к gb_testfl
	$mysqli = $DbService->getConnection();

	$query = $mysqli->prepare('
        UPDATE `pdf_uploads` SET `position` = ? WHERE `id` = ?'
    );
    $query->bind_param('si', $position, $post['id']);
    $query->execute();
    
	echo json_encode(array(
		'result' => true,
	    'position' => $position   
	));
}
