const AUTO_OPEN_FEEDBACK_DELAY_MS = 3000;

const sdkNamespace = window.WidgetSDK;
// Guard against missing SDK script.
const sdk = sdkNamespace ? sdkNamespace.create({debug: true}) : null;

if (sdk) {
    // Expose for easier debugging in the console.
    window.widgetSdk = sdk;
}

// Parse JSON when possible, otherwise return a trimmed string.
const parseMaybeJson = (value) => {
    if (value === undefined || value === null) {
        return undefined;
    }


    const trimmed = String(value).trim();

    if (!trimmed) {
        return undefined;
    }

    try {
        return JSON.parse(trimmed);
    } catch (_) {
        return trimmed;
    }
};

let objectState = {};

// Safe shallow-ish equality for diffing; falls back to JSON compare for objects.
const valuesEqual = (left, right) => {
    if (left === right) {
        return true;
    }

    if (left && right && typeof left === 'object' && typeof right === 'object') {
        try {
            return JSON.stringify(left) === JSON.stringify(right);
        } catch (_) {
            return false;
        }
    }

    return false;
};

// Compute changed keys between two object states.
const diffs = (oldState, newState) => {
    const result = new Map();

    if (!newState || typeof newState !== 'object') {
        return result;
    }

    for (const key in newState) {
        if (Object.prototype.hasOwnProperty.call(newState, key)) {
            const oldValue = oldState && Object.prototype.hasOwnProperty.call(oldState, key) ? oldState[key] : undefined;

            if (!oldState || !Object.prototype.hasOwnProperty.call(oldState, key)
                || !valuesEqual(newState[key], oldValue)) {
                result.set(key, newState[key]);
            }
        }
    }

    if (oldState && typeof oldState === 'object') {
        for (const key in oldState) {
            if (Object.prototype.hasOwnProperty.call(oldState, key)
                && (!newState || !Object.prototype.hasOwnProperty.call(newState, key))) {
                result.set(key, '<deleted>');
            }
        }
    }

    return result;
};

// Format diff map for logging.
const formatDiffs = (map) => {
    if (!map || map.size === 0) {
        return 'objectState: no changes';
    }

    const lines = [];

    map.forEach((value, key) => {
        if (value && typeof value === 'object') {
            lines.push(`${key} = {...}`);
        } else {
            lines.push(`${key} = ${value}`);
        }
    });

    return `objectState changes:\n${lines.join('\n')}`;
};

if (!sdk) {
    widgetLog('SDK init skipped', {reason: 'WidgetSDK is not available'});
} else {
    widgetLog('SDK initialized', {debug: true});
}

// Auto-open feedback if the Open event provides a correlation id.
const maybeAutoOpenFeedback = (openMessage) => {
    const resolvedId = openMessage?.messageId;

    if (resolvedId === null || resolvedId === undefined) {
        widgetLog('auto openFeedback skipped', {reason: 'missing correlationId'});

        return;
    }

    setTimeout(() => {
        const res = sdk ? sdk.openFeedback(resolvedId) : null;

        widgetLog('auto openFeedback sent', res);
    }, AUTO_OPEN_FEEDBACK_DELAY_MS);
};
