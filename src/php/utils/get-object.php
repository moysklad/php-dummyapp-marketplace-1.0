<?php
require_once __DIR__ . '/../lib/lib.php';

$entitiesMap = [
    'customerorder' => 'Заказ покупателя',
    'invoiceout' => 'Счет покупателю',
];

$entity = $_GET['entity'];
$objectId = $_GET['objectId'];
$accountId = $_GET['accountId'];

$app = AppInstance::loadApp($accountId);

$object = jsonApi()->getObject($entity, $objectId);

echo $entitiesMap[$entity] . ' ' . $object->name;
