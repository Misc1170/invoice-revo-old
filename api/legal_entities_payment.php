<?php 

require_once __DIR__ . '/../Src/init.php';

$post = file_get_contents('php://input');
if ($post == ''){
    return;
}
$post = json_decode($post, 1);
$mysqli = $DbService->getConnection();

$email = trim(strval($post['email']));
$fullname = trim(strval($post['fullname']));

$query = $mysqli->prepare('
    INSERT INTO `legal_entities_payment` 
    (`email`, `fullname`) 
        VALUES 
    (?, ?)
');
$query->bind_param('ss', $email, $fullname);

echo json_encode(array('result' => $query->execute()));