if (!sdk) {
    widgetLog('SDK events skipped', {reason: 'WidgetSDK is not available'});
} else {
    sdk.onOpen(message => {
        widgetLog('Event: Open', message);
        maybeAutoOpenFeedback(message);
    });
    sdk.onOpenPopup(message => widgetLog('Event: OpenPopup', message));
    sdk.onChange(message => {
        widgetLog('Event: Change', message);

        if (!message || !message.objectState) {
            widgetLog('Change ignored', {reason: 'missing objectState'});
            return;
        }

        const diffMap = diffs(objectState, message.objectState);

        widgetLog('Event: Change (diff)', formatDiffs(diffMap));

        objectState = message.objectState;
    });
    sdk.onSave(message => widgetLog('Event: Save', message));
}
