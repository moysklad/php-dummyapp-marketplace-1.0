type WidgetEventName = 'Open' | 'OpenPopup' | 'Save' | 'Change';

interface WidgetHostMessage {
    name: WidgetEventName;
    messageId?: number;
    correlationId?: number;
    errors?: Array<{ error: string; [key: string]: unknown }>;

    [key: string]: unknown;
}

interface WidgetSdkOptions {
    debug?: boolean;
}

type WidgetSubscribe = () => void;

interface WidgetSdk {
    /** Opens the product group selector. */
    selectGoodFolder(): Promise<WidgetHostMessage>;

    /** Opens a standard dialog. */
    showDialog(text: string, buttons?: Array<Record<string, unknown>>): Promise<WidgetHostMessage>;

    /** Navigate inside host UI. target defaults to 'blank'. */
    navigateTo(path: string, target?: 'blank' | 'self'): Promise<WidgetHostMessage>;

    /** Requests document update. */
    update(updateState: Record<string, unknown>): Promise<WidgetHostMessage>;

    /** Sends OpenFeedback message. */
    openFeedback(openMessageId?: number): WidgetHostMessage | null;

    /** Marks state dirty. */
    setDirty(openMessageId?: number): WidgetHostMessage | null;

    /** Clears dirty flag. */
    clearDirty(): WidgetHostMessage;

    /** Sends validation result. */
    validationFeedback(valid: boolean, messageText?: string, changeMessageId?: number): WidgetHostMessage | null;

    /** Shows a custom popup. */
    showPopup(popupName: string, popupParameters?: Record<string, unknown>): Promise<WidgetHostMessage>;

    /** Closes a custom popup. */
    closePopup(popupResponse?: Record<string, unknown>): WidgetHostMessage;

    /** Generic event subscription. */
    on(eventName: WidgetEventName, callback: (message: WidgetHostMessage) => void): WidgetSubscribe;

    onOpen(callback: (message: WidgetHostMessage) => void): WidgetSubscribe;

    onOpenPopup(callback: (message: WidgetHostMessage) => void): WidgetSubscribe;

    onSave(callback: (message: WidgetHostMessage) => void): WidgetSubscribe;

    onChange(callback: (message: WidgetHostMessage) => void): WidgetSubscribe;

    off(eventName: WidgetEventName, callback: (message: WidgetHostMessage) => void): void;

    /** Cleanup. */
    destroy(): void;
}

interface WidgetSdkNamespace {
    version: string;

    create(options?: WidgetSdkOptions): WidgetSdk;

    WidgetSDKInstance: new (options?: WidgetSdkOptions) => WidgetSdk;
}

interface Window {
    WidgetSDK: WidgetSdkNamespace;
}

declare const window: Window;
