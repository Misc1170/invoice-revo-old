<?php

function pre($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

$data['link'] = "?InvoiceId=14504&Email=ash%40fluid-line.ru&AccountId=ash%40fluid-line.ru&Amount=18823.2&invoice=ACWC-6M-4P%246%241045.2%246271.2%2F%2FACWC-8M-4P%245%241524%247620%2F%2FACCA-20M%242%242466%244932";

$invoiceArray = array();
$queryString = urldecode(trim(strval($data['link']), '?'));

$fetchPay = explode('&', $queryString);
foreach ($fetchPay as $rowInner) {
    $exp = explode('=', $rowInner);
    $invoiceArray[lcfirst($exp[0])] = $exp[1];
}

pre($invoiceArray);

foreach ($invoiceArray as $k => $v)
    $$k = $v;

include_once __DIR__ . '/oplata2.php';
$pay_link = $linka;

echo $pay_link;

?>