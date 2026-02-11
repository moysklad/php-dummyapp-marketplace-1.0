<div>
    <h2>standard-dialogs</h2>
    <div class="row field-row">
        <label for="dialogText">Текст диалога</label>
        <input id="dialogText" value="Hello from SDK">
    </div>
    <div class="row field-row">
        <label for="dialogButtons">Кнопки диалога (JSON)</label>
        <textarea
            id="dialogButtons">[{ "name": "Yes", "caption": "Да, удалить" },{ "name": "No", "caption": "Нет" }]</textarea>
    </div>
    <div class="row">
        <button class="btn" id="btnDialog">Открыть</button>
    </div>
</div>
<script>
    document.getElementById('btnDialog').addEventListener('click', async () => {
        const text = document.getElementById('dialogText').value.trim() || 'Dialog';
        const buttonsPayload = parseMaybeJson(document.getElementById('dialogButtons').value);

        try {
            const normalizedButtons = Array.isArray(buttonsPayload)
                ? buttonsPayload
                : (buttonsPayload && Array.isArray(buttonsPayload.buttons) ? buttonsPayload.buttons : undefined);
            const res = await sdk.showDialog(text, normalizedButtons);

            widgetLog('showDialog response', res);
        } catch (e) {
            widgetLog('showDialog error', {message: e.message, name: e.name});
        }
    });
</script>
