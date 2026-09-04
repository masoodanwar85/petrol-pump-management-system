import { clearSession, getToken } from './auth';

const BASE = '/api/v1';

export class ApiError extends Error {
    constructor(message, { status = 422, errors = {} } = {}) {
        super(message);
        this.status = status;
        this.errors = errors;
    }
}

export async function api(path, { method = 'GET', body } = {}) {
    const headers = {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    };

    const token = getToken();

    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    const response = await fetch(`${BASE}${path}`, {
        method,
        headers,
        body: body === undefined ? undefined : JSON.stringify(body),
    });

    const payload = await response.json().catch(() => ({}));

    if (response.status === 401) {
        clearSession();

        if (!window.location.pathname.endsWith('/login')) {
            window.location.assign('/admin/login');
        }

        throw new ApiError(payload.message || 'Please sign in again.', { status: 401 });
    }

    if (!response.ok || payload.success === false) {
        throw new ApiError(payload.message || 'Request failed.', {
            status: response.status,
            errors: payload.errors || {},
        });
    }

    return payload;
}
