<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">

    <title>DummyApp Popup</title>
    <meta name="description" content="DummyApp Popup for Apps Catalog of MoySklad">

    <style>
        body {
            line-height: 1;
            font-size: 12px;
            height: 100%;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .hint {
            cursor: default;
        }
        .main-container {
            display: flex;
            flex-direction: column;
            height: calc(100vh);
            overflow: hidden;
        }
        .content {
            flex-grow: 1;
            overflow: auto;
            padding: 20px;
        }
        .buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 20px;
            border-top: 1px solid #eee;
        }
    </style>

    <script>
        var hostWindow = window.parent;

        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;

            logReceivedMessage(receivedMessage);
        });

        let messageId = 1;
        function sendClosePopupMsg(popupResponse) {
            let msg = {
                name: "ClosePopup",
                messageId: messageId++,
                popupResponse: popupResponse
            }
            logSendingMessage(msg);
            parent.postMessage(msg, '*');
        }

        function onSave() {
            let popupResponse = {
                button: "save"
            }
            sendClosePopupMsg(popupResponse)
        }

        function onClose() {
            let popupResponse = {
                button: "close"
            }
            sendClosePopupMsg(popupResponse)
        }

        function logReceivedMessage(msg) {
            logMessage("→ Received", msg)
        }

        function logSendingMessage(msg) {
            logMessage("← Sending", msg)
        }

        function logMessage(prefix, msg) {
            var messageAsString = JSON.stringify(msg);
            console.log(prefix + " message: " + messageAsString);
            addMessage(prefix.toUpperCase() + " " + messageAsString);
        }

        function addMessage(item) {
            var messages = window.document.getElementById("messages");
            messages.innerHTML = item + "<br/>" + messages.innerHTML;
            messages.title += item + "\n";
        }

        function toggleBorders(value) {
            body().className = value ? "borders" : "";
        }

        function showDimensions() {
            var dimensions = window.document.getElementById("dimensions");
            dimensions.innerText = body().offsetWidth + " x " + body().offsetHeight
        }
        function body() {
            return window.document.body;
        }
    </script>
</head>
<body>
<div class="main-container">
    <div>
        <!-- Данные для отладки -->
        <p><b title="Информацию о текущем пользователе кастомное окно может получить на своем бэкенде через Vendor API, используя contextKey">Текущий пользователь <span class="hint">(?)</span>:</b> <?=$uid?> (<?=$fio?>)</p>

        <b title="Лог входящих от хост-окна и исходящих от окна сообщений (коммуникация через Window.postMessage). Здесь лог отображается в обратном порядке (последние сообщения сверху), при наведении на текст сообщений можно посмотреть все сообщения в прямом порядке (последние сообщения в конце)">Сообщения <span class="hint">(?)</span>:</b>
        <div id="messages"></div>
    </div>

    <div class="content">
        <!--Разместите здесь содержимое -->
    </div>
    <div class="buttons">
        <button class="button button--success" onclick="onSave()">Сохранить</button>
        <button class="button" onclick="onClose()">Отмена</button>
    </div>
</div>
</body>
</html>
