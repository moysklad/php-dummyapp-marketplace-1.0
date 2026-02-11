<div>
    <h2>navigation-service</h2>
    <div class="row field-row">
        <label for="navigatePath">Путь</label>
        <input id="navigatePath" value="#customerorder?sort=o.moment%20d">
    </div>
    <div class="row">
        <button class="btn" id="btnNavigate">Перейти</button>
    </div>
</div>
<script>
    document.getElementById('btnNavigate').addEventListener('click', async () => {
        const path = document.getElementById('navigatePath').value.trim() || '/';

        try {
            const res = await sdk.navigateTo(path, 'blank');

            widgetLog('navigateTo response', res);
        } catch (e) {
            widgetLog('navigateTo error', {message: e.message, name: e.name});
        }
    });
</script>
