<?php

$dirRoot = '../';

require_once '../lib.php';

$entitiesMap = [
    'counterparty' => 'Контрагент',
    'customerorder' => 'Заказ покупателя',
    'demand' => 'Отгрузка',
];

$entity = $_GET['entity'];
$objectId = $_GET['objectId'];
$accountId = $_GET['accountId'];

$app = AppInstance::loadApp($accountId);

$object = jsonApi()->getObject($entity, $objectId);

echo $entitiesMap[$entity] . ' ' . $object->name;