<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">

    <title>DummyApp: <?=$contextName?></title>
    <meta name="description" content="DummyApp widget for Marketplace of MoySklad">
    <meta name="author" content="onekludov@moysklad.ru">

    <style>
        html {
            height: 100%;
        }
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
        .borders {
            border: 1px solid silver;
        }
    </style>

    <script>
        var hostWindow = window.parent;

        window.addEventListener("message", function(event) {
            var receivedMessage = event.data;

            logReceivedMessage(receivedMessage);

            if (receivedMessage.name === 'Open') {
                var oReq = new XMLHttpRequest();
                oReq.addEventListener("load", function() {
                    window.document.getElementById("object").innerHTML = this.responseText;
                });
                // В демо приложении отсутствует авторизация (между виджетом и бэкендом) - в реальных приложениях не делайте так (должна быть авторизация)!
                oReq.open("GET", "<?=$getObjectUrl?>" + receivedMessage.objectId);
                oReq.send();

                window.setTimeout(function() {
                    var sendingMessage = {
                        name: "OpenFeedback",
                        correlationId: receivedMessage.messageId
                    };
                    logSendingMessage(sendingMessage);
                    hostWindow.postMessage(sendingMessage, '*');

                }, getOpenFeedbackDelay());
            }
        });

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

        function getOpenFeedbackDelay() {
            return window.document.getElementById("openFeedbackDelay").value
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
<p><b title="Информацию о текущем пользователе виджет может получить на своем бэкенде через Vendor API, используя contextKey">Текущий пользователь <span class="hint">(?)</span>:</b> <?=$uid?> (<?=$fio?>)</p>

<p><b title="Используя objectId, переданный в сообщении Open, можем получить через JSON API открытую пользователем сущность/документ">Открыт объект <span class="hint">(?)</span>:</b> <span id="object"></span></p>

<p><b title="Синтетическая задержка, позволяющая посмотреть как работает функционал OpenFeedback">Задержка OpenFeedback, мс <span class="hint">(?)</span>:</b> <input type="text" id="openFeedbackDelay" value="300"></p>

<p><b title="Здесь можно посмотреть границы и размеры содержимого виджета. Ширина одинаковая у всех виджетов, высота на текущий момент задается статически для виджета в дескрипторе приложения">Cодержимое <span class="hint">(?)</span></b>:
    границы <input type="checkbox" onclick="toggleBorders(this.checked)">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ширина x высота, px: <span id="dimensions"><button onclick="showDimensions()">показать</button></span>
    <span class="hint" title="Обратите внимание, что геометрические свойства DOM-элементов типа scrollHeight равны 0 или null до момента снятия заглушки в хост-окне (например, в обработчике входящих postMessage-сообщений и даже после отправки сообщения OpenFeedback)">(?)</span></p>

<b title="Лог входящих от хост-окна и исходящих от виджета сообщений (коммуникация через Window.postMessage). Здесь лог отображается в обратном порядке (последние сообщения сверху), при наведении на текст сообщений можно посмотреть все сообщения в прямом порядке (последние сообщения в конце)">Сообщения <span class="hint">(?)</span>:</b>
<div id="messages"></div>
</body>
</html>