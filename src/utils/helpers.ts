
export function convertToTitleCase(str: string) {
    return str.toLowerCase().replace(/_/g, ' ').replace(/(^|\s)\w/g, function (match: string) {
        return match.toUpperCase();
    });
}

// Event emitter to emit PrepareEdit to the parent component
export const emitPrepareEdit = (row: any, action: any, data: any) => {
    const event = new CustomEvent('prepareEdit', {
        detail: { row, action, data }
    });
    window.dispatchEvent(event);
};

// Event emitter to emit statusUpdate to the parent component
export const emitStatusUpdate = (e: Event) => {
    e.preventDefault()
    const event = new CustomEvent('statusUpdate', {
        detail: e,
    });
    window.dispatchEvent(event);
};

// Event emitter to emit emitAjaxPost to the parent component
export const emitAjaxPost = (e: any) => {
    e.preventDefault()
    const event = new CustomEvent('ajaxPost', {
        detail: e,
    });
    window.dispatchEvent(event);
};

// Event emitter to emit emitAjaxPost to the parent component
export const emitAjaxPostDone = (response: any) => {
    const event = new CustomEvent('ajaxPostDone', {
        detail: response,
    });
    window.dispatchEvent(event);
};

export const baseURL = (uri: string) => import.meta.env.VITE_APP_BASE_API + ('/' + uri).replace(/\/+/, '/')

export const environment: 'local' | 'production' = import.meta.env.VITE_APP_ENV || 'local'

export const convertToLaravelPattern = (uri:string) => {
    // Replace :id with {id}
    const laravelPattern = uri.replace(/:\w+/g, (match) => `{${match.substring(1)}}`);
    return laravelPattern;
  };