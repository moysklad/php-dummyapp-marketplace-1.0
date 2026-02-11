<div>
    <h2>good-folder-selector</h2>
    <div class="row">
        <button class="btn" id="btnSelectFolder">Выбрать</button>
    </div>
</div>
<script>
    document.getElementById('btnSelectFolder').addEventListener('click', async () => {
        try {
            const res = await sdk.selectGoodFolder();

            widgetLog('selectGoodFolder response', res);
        } catch (e) {
            widgetLog('selectGoodFolder error', {message: e.message, name: e.name});
        }
    });
</script>
