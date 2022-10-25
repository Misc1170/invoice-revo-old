<?php 


$post = file_get_contents('php://input');
if ($post)
    $post = json_decode($post, 1);

echo json_encode(array(
    'data' => $post
));

?>