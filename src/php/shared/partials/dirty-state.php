<div>
    <h2>dirty-state</h2>
    <div class="row grid-2">
        <button class="btn" id="btnSetDirty">Установить</button>
        <button class="btn" id="btnClearDirty">Очистить</button>
    </div>
</div>
<script>
    document.getElementById('btnSetDirty').addEventListener('click', () => {
        const res = sdk.setDirty();

        widgetLog('setDirty sent', res);
    });

    document.getElementById('btnClearDirty').addEventListener('click', () => {
        const res = sdk.clearDirty();

        widgetLog('clearDirty sent', res);
    });
</script>
