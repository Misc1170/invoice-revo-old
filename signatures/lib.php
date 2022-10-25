<?php
function db_open($dbname)
{
        $db = new PDO('sqlite:'.$dbname);
        $db->exec("CREATE TABLE IF NOT EXISTS contacts (
                    id INTEGER PRIMARY KEY,
                    fio TEXT,
                    fio_en TEXT,
                    email TEXT,
                    doljnost TEXT,
                    doljnost_en TEXT,
                    phone TEXT,
                    phone_en TEXT)");
    return $db;
}

function getContacts($db){
	$result = $db->query('SELECT * FROM `contacts`');
    while($contacts[] = $result->fetch(PDO::FETCH_ASSOC)){}

    $contacts = array_diff($contacts, array(''));

    if($contacts[0] == false)
        return;

    return $contacts;
}