export function money(value) {
    return Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

export function liters(value) {
    return Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 3,
        maximumFractionDigits: 3,
    });
}

export function when(value) {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString();
}

export function day(value) {
    if (!value) {
        return '—';
    }

    return String(value).slice(0, 10);
}

export function enumLabel(value) {
    if (!value) {
        return '—';
    }

    const raw = typeof value === 'object' ? value.value || value.name : value;

    return String(raw).replaceAll('_', ' ');
}
