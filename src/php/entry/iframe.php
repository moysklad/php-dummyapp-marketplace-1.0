<?php
require_once __DIR__ . '/../lib/lib.php';

$context = require __DIR__ . '/../lib/user-context-loader.inc.php';

$contextName = 'IFRAME';

$app = AppInstance::loadApp($context['accountId']);

$infoMessage = $app->infoMessage;
$store = $app->store;
$isSettingsRequired = $app->status != AppInstance::ACTIVATED;
$storesValues = [];

if ($context['isAdmin']) {
    $stores = jsonApi()->stores();

    foreach ($stores->rows as $v) {
        $storesValues[] = $v->name;
    }
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DummyApp</title>
    <meta name="description" content="DummyApp for Apps Catalog of MoySklad">
    <link rel="stylesheet" href="../shared/styles/widget.css">
    <link rel="stylesheet" href="../shared/styles/iframe.css">
    <script type="text/javascript"
            src="https://apps-api.moysklad.ru/js/ns/appstore/app/v1/moysklad-iframe-expand-3.js"></script>
</head>
<body>
<main class="iframe-layout">
    <section class="panel">
        <h2>Информация о пользователе</h2>
        <ul class="info-list">
            <li>Текущий пользователь: <?= $context['uid'] ?> (<?= $context['fio'] ?>)</li>
            <li>Идентификатор аккаунта: <?= $context['accountId'] ?></li>
            <li>Уровень доступа: <b><?= $context['isAdmin'] ? 'администратор аккаунта' : 'простой пользователь' ?></b>
            </li>
        </ul>
        <div class="panel-divider"></div>
        <h2>Состояние решения</h2>
        <div class="status-box <?= $isSettingsRequired ? 'status-required' : 'status-ready' ?>">
            <div class="status-title">
                <?= $isSettingsRequired ? 'ТРЕБУЕТСЯ НАСТРОЙКА' : 'РЕШЕНИЕ ГОТОВО К РАБОТЕ' ?>
            </div>
            <?php if (!$isSettingsRequired) { ?>
                <p>
                    Сообщение: <?= $infoMessage ?><br>
                    Выбран склад: <?= $store ?>
                </p>
            <?php } ?>
        </div>
    </section>
    <section class="panel">
        <h2>Форма настроек</h2>
        <?php if ($context['isAdmin']) { ?>
            <form method="post" action="../api/update-settings.php">
                <div class="row field-row">
                    <label for="infoMessage">Укажите сообщение</label>
                    <input id="infoMessage" type="text" name="infoMessage">
                </div>
                <div class="row field-row">
                    <label for="store">Выберите склад</label>
                    <select id="store" name="store">
                        <?php foreach ($storesValues as $v) { ?>
                            <option value="<?= $v ?>"><?= $v ?></option>
                        <?php } ?>
                    </select>
                </div>
                <input type="hidden" name="accountId" value="<?= $context['accountId'] ?>"/>
                <button class="btn" type="submit">Сохранить</button>
            </form>
        <?php } else { ?>
            <p class="muted">Настройки доступны только администратору аккаунта</p>
        <?php } ?>
    </section>
</main>
</body>
</html>
