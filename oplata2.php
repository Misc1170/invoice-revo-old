<?php
ini_set('display_errors', 'Off');

if (!empty($invoice) && !empty($invoiceId)) {

    $invoice_data = $json_data = array();

    // обрабатываем invoice 
    $invoice_ex = explode('//', $invoice);
    foreach ($invoice_ex as $key => $val) {

        // отделяем товар от количества 
        $val_ex = explode('$', $val);

        // формируем массив товаров 
        if (sizeof($val_ex) == 4) {
            $invoice_data[] = array(
                'label' => $val_ex[0],
                'quantity' => $val_ex[1],
                'price' => $val_ex[2],
                'amount' => $val_ex[3],
                'vat' => 18,
                'method' => 1,
                'object' => 1,
                'measurementUnit' => 'шт',
            );
        }

    }

    // формируем массив под JSON 
    if (!empty($invoice_data)) {

        $json_data = array(
            'amount' => $amount,
            'InvoiceId' => $invoiceId,
            'AccountId' => $accountId,
            'Currency' => 'RUB',
            'SendEmail' => true,
            'Description' => 'Оплата на сайте',
            'JsonData' => array(
                'cloudPayments' => array(
                    'customerReceipt' => array(
                        'Items' => $invoice_data,
                        'taxationSystem' => 0,
                        'email' => $email,
                        'phone' => '',
                        'amounts' => array(
                            'electronic' => $amount,
                        ),
                    ),
                ),
            ),
        );

        // отдаём JSON 
        header("Content-type: application/json; charset=utf-8");
        $novinka = json_encode(
            $json_data,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | 128
        );

    }
} else {
    $linka = false;
    return false;
}


$url = 'https://api.cloudpayments.ru/orders/create';
$appId = 'pk_084eec6c4d5ed5828942d8a47ff1c';
$ApiKey = 'c66bc985a077e5f53d4814a8e151fb4b';
$headers = array(
    "Content-Type: application/json",
    "X-Parse-Application-Id: " . $appId,
    "X-Parse-REST-API-Key: " . $ApiKey
);

$rest = curl_init();
curl_setopt($rest, CURLOPT_URL, $url);
curl_setopt($rest, CURLOPT_POST, 1);
curl_setopt($rest, CURLOPT_POSTFIELDS, $novinka);
curl_setopt($rest, CURLOPT_HTTPHEADER, $headers);
curl_setopt($rest, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($rest, CURLOPT_RETURNTRANSFER, true);
curl_setopt($rest, CURLOPT_FOLLOWLOCATION, true);
//curl_setopt($rest, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($rest, CURLOPT_USERPWD, $appId . ":" . $ApiKey);

$response = curl_exec($rest);

curl_close($rest);
$res = json_decode($response, true);
$linka = $res['Model']['Url'];

?>