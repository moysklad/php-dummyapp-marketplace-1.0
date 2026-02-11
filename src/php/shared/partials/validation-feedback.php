<div>
    <h2>validation-feedback</h2>
    <div class="row field-row">
        <label for="validationPayload">Параметры валидации (JSON или text)</label>
        <textarea id="validationPayload">{ "name": "ValidationFeedback", "correlationId": 1, "messageId": 1, "valid": false, "message": "Нужно больше печенья" }</textarea>
    </div>
    <div class="row">
        <button class="btn" id="btnValidation">Подтвердить</button>
    </div>
</div>
<script>
    document.getElementById('btnValidation').addEventListener('click', () => {
        const payload = parseMaybeJson(document.getElementById('validationPayload').value);

        let valid = false;
        let message = undefined;
        let changeMessageId = undefined;

        if (payload && typeof payload === 'object' && !Array.isArray(payload)) {
            if (payload.valid !== undefined) {
                valid = payload.valid;
            }

            if (payload.message !== undefined) {
                message = payload.message;
            }

            if (payload.changeMessageId !== undefined) {
                changeMessageId = payload.changeMessageId;
            }

            if (payload.correlationId !== undefined) {
                changeMessageId = payload.correlationId;
            }
        } else if (payload !== undefined) {
            message = String(payload);
        }

        const res = sdk.validationFeedback(valid, message, changeMessageId);

        widgetLog('validationFeedback sent', res);
    });
</script>
