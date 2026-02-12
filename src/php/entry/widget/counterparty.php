<?php
$contextName = 'COUNTERPARTY-WIDGET';
$entity = 'counterparty';

require_once 'widget.inc.php';
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>DummyApp: <?= $contextName ?></title>
    <link rel="stylesheet" href="../../shared/styles/widget.css">
    <script type="text/javascript"
            src="https://cdn.jsdelivr.net/npm/@moysklad-official/js-widget-sdk@1/dist/widget.min.js"></script>
</head>
<body>
<main>
    <section class="panel settings">
        <?php include __DIR__ . '/../../shared/partials/good-folder-selector.php'; ?>
        <div class="panel-divider"></div>
        <?php include __DIR__ . '/../../shared/partials/navigation-service.php'; ?>
        <div class="panel-divider"></div>
        <?php include __DIR__ . '/../../shared/partials/standard-dialogs.php'; ?>
        <div class="panel-divider"></div>
        <?php include __DIR__ . '/../../shared/partials/dirty-state.php'; ?>
        <div class="panel-divider"></div>
        <?php include __DIR__ . '/../../shared/partials/popups.php'; ?>
    </section>
    <section class="panel output">
        <?php include __DIR__ . '/../../shared/partials/log.php'; ?>
    </section>
</main>
<script src="../../shared/scripts/widget-utils.js"></script>
<script src="../../shared/scripts/widget.js"></script>
</body>
</html>
