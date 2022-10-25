<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/assets/snippets/fluid/lib.php';
if(!isManager())
    die('Доступ закрыт');