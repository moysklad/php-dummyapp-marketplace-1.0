<div>
    <h2>Popups</h2>
    <div class="row field-row">
        <label for="popupName">Название попапа</label>
        <input id="popupName" value="some-popup">
    </div>
    <div class="row field-row">
        <label for="popupParams">Параметры попапа (JSON)</label>
        <textarea id="popupParams">{ "foo": "bar" }</textarea>
    </div>
    <div class="row btn-row">
        <button class="btn" id="btnShowPopup">Открыть</button>
        <button class="btn" id="btnClosePopup">Закрыть</button>
    </div>
</div>
<script>
    document.getElementById('btnShowPopup').addEventListener('click', async () => {
        const name = document.getElementById('popupName').value.trim() || 'popup';
        const params = parseMaybeJson(document.getElementById('popupParams').value);

        try {
            const res = await sdk.showPopup(name, params);

            widgetLog('showPopup response', res);
        } catch (e) {
            widgetLog('showPopup error', {message: e.message, name: e.name});
        }
    });

    document.getElementById('btnClosePopup').addEventListener('click', () => {
        const res = sdk.closePopup({ok: true});

        widgetLog('closePopup sent', res);
    });
</script>
