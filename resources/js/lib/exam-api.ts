function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

export async function examJson<T>(
    url: string,
    options: RequestInit = {},
): Promise<{ ok: true; data: T } | { ok: false; message: string }> {
    const response = await fetch(url, {
        ...options,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': getCsrfToken(),
            ...(options.headers as Record<string, string>),
        },
        credentials: 'same-origin',
    });

    const body = await response.json().catch(() => ({}));

    if (!response.ok) {
        return {
            ok: false,
            message: (body as { message?: string }).message ?? 'Произошла ошибка',
        };
    }

    return { ok: true, data: body as T };
}
