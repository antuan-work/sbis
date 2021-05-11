<?php
set_time_limit(300);

/**
 * Указать реквизиты
 */
$sbis_configs = [
    'app_client_id' => '',
    'app_secret'    => '',
    'secret_key'    => '',
];

/**
 * Рассматриваемый документ
 */
$doc_guid = '9257a39c-419a-434a-b19f-7271be7eee50';

require './Sbis.php';

$sbis = new Sbis($sbis_configs);
$doc = $sbis->get_doc($doc_guid);

foreach ($doc['Вложение'] as $k => $file) {
    $file_content = $sbis->get_file($file['СсылкаНаPDF']);
    print_r($file_content);
    exit;
}