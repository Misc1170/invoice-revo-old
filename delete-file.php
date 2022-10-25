<?php

$post = file_get_contents('php://input');
if ($post)
    $post = json_decode($post, 1);

$file = __DIR__ .  '/upload-pdfs/files/' . strval($post['file']);
sleep(1);
if(file_exists($file))
    unlink($file);

echo json_encode(array(
    'file' => $file,
    'result' => !file_exists($file)
));
?>