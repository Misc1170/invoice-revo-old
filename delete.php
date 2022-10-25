<?php
$post = file_get_contents('php://input');
if ($post)
    $post = json_decode($post, 1);

$file = strval($post['file']);
unlink($file);

echo json_encode(array(
    'file' => $file,
    'result' => !file_exists($file)
));
?>