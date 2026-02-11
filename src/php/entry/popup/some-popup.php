<?php
require_once 'popup.inc.php';
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DummyApp Popup</title>
    <link rel="stylesheet" href="../../shared/styles/widget.css">
    <link rel="stylesheet" href="../../shared/styles/popup.css">
    <script type="text/javascript"
            src="https://cdn.jsdelivr.net/npm/@moysklad-official/js-widget-sdk/dist/widget.min.js"></script>
    <script type="text/javascript"
            src="https://apps-api.moysklad.ru/js/ns/appstore/app/v1/moysklad-iframe-expand-3.js"></script>
</head>
<body>
<main>
    <section class="panel settings">
        <div class="tabs" role="tablist" aria-label="SDK sections">
            <button class="tab active" data-tab="good-folder" role="tab" aria-selected="true">Выбор группы товаров
            </button>
            <button class="tab" data-tab="navigation" role="tab" aria-selected="false">Навигация</button>
            <button class="tab" data-tab="dialogs" role="tab" aria-selected="false">Диалог</button>
            <button class="tab" data-tab="popups" role="tab" aria-selected="false">Попап</button>
        </div>
        <div class="tab-panel active" data-tab-panel="good-folder" role="tabpanel">
            <?php include __DIR__ . '/../../shared/partials/good-folder-selector.php'; ?>
        </div>
        <div class="tab-panel" data-tab-panel="navigation" role="tabpanel">
            <?php include __DIR__ . '/../../shared/partials/navigation-service.php'; ?>
        </div>
        <div class="tab-panel" data-tab-panel="dialogs" role="tabpanel">
            <?php include __DIR__ . '/../../shared/partials/standard-dialogs.php'; ?>
        </div>
        <div class="tab-panel" data-tab-panel="popups" role="tabpanel">
            <?php include __DIR__ . '/../../shared/partials/popups.php'; ?>
        </div>
    </section>
    <section class="panel logs">
        <?php include __DIR__ . '/../../shared/partials/log.php'; ?>
    </section>
</main>
<script src="../../shared/scripts/widget-utils.js"></script>
<script src="../../shared/scripts/widget.js"></script>
<script src="../../shared/scripts/popup-tabs.js"></script>
</body>
</html>
