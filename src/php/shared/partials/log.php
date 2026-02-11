<div>
    <h2>Логи</h2>
    <div id="log"></div>
</div>
<script>
    const logEl = document.getElementById('log');

    // Log to UI panel when present, fall back to console otherwise.
    const widgetLog = (label, payload) => {
        const ts = new Date().toISOString().replace('T', ' ').replace('Z', '');
        const data = payload ? JSON.stringify(payload, null, 2) : '';
        const message = `[${ts}] ${label}\n${data}\n\n`;

        if (logEl) {
            logEl.textContent = message + logEl.textContent;
        } else {
            console.log(message);
        }
    };

    window.widgetLog = widgetLog;
</script>
