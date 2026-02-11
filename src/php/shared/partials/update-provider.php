<div>
    <h2>update-provider</h2>
    <div class="row field-row">
        <label for="updatePayload">Параметры обновления (JSON or text)</label>
        <textarea id="updatePayload">{ "name": "1" }</textarea>
    </div>
    <div class="row">
        <button class="btn" id="btnUpdate">Обновить</button>
    </div>
</div>
<script>
    document.getElementById('btnUpdate').addEventListener('click', async () => {
        const payload = parseMaybeJson(document.getElementById('updatePayload').value);

        try {
            const res = await sdk.update(payload);

            widgetLog('update response', res);
        } catch (e) {
            widgetLog('update error', {message: e.message, name: e.name});
        }
    });
</script>
