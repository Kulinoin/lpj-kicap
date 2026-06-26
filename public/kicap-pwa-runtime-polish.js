(() => {
    'use strict';

    const state = {
        loading: false,
        loaded: false,
        activities: [],
        lastRun: 0,
    };

    const unwrapActivities = (payload) => {
        if (Array.isArray(payload)) return payload;
        if (Array.isArray(payload?.data?.activities)) return payload.data.activities;
        if (Array.isArray(payload?.activities)) return payload.activities;
        if (Array.isArray(payload?.data)) return payload.data;
        return [];
    };

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const dateTime = (value) => {
        const date = new Date(value);

        if (Number.isNaN(date.getTime())) return '';

        return date.toLocaleString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    const fetchActivities = async () => {
        if (state.loading) return state.activities;

        state.loading = true;

        try {
            const response = await fetch('/api/app/activity-history?fresh=' + Date.now(), {
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error('Activity history API error: ' + response.status);
            }

            const payload = await response.json();

            state.activities = unwrapActivities(payload)
                .filter((item) => item?.at)
                .sort((left, right) => new Date(right.at) - new Date(left.at));

            state.loaded = true;

            return state.activities;
        } catch (error) {
            console.warn('Kicap activity history failed:', error);
            return [];
        } finally {
            state.loading = false;
        }
    };

    const cardHtml = (activity) => `
        <article class="activity-runtime-card">
            <div class="activity-runtime-icon" aria-hidden="true">${escapeHtml(activity.icon || '•')}</div>
            <div class="activity-runtime-body">
                <div class="activity-runtime-top">
                    <div>
                        <h3>${escapeHtml(activity.title || 'Aktivitas user')}</h3>
                        <p>${escapeHtml(activity.subtitle || 'Event')}</p>
                    </div>
                    <time>${escapeHtml(dateTime(activity.at))}</time>
                </div>
                ${activity.meta ? `<div class="activity-runtime-meta">${escapeHtml(activity.meta)}</div>` : ''}
            </div>
        </article>
    `;

    const enhanceHistoryDom = async () => {
        const now = Date.now();

        if (now - state.lastRun < 350) return;

        state.lastRun = now;

        const container = document.querySelector('.activity-history');

        if (!container) return;

        if (container.dataset.runtimePolished === 'done' && state.loaded) return;

        container.classList.add('runtime-polished');
        container.dataset.runtimePolished = 'loading';

        const head = container.querySelector('.profile-subpage-head');

        Array.from(container.children).forEach((child) => {
            if (child !== head) child.remove();
        });

        const loading = document.createElement('div');
        loading.className = 'activity-runtime-loading';
        loading.textContent = 'Memuat aktivitas user...';
        container.appendChild(loading);

        const activities = await fetchActivities();

        loading.remove();

        if (!activities.length) {
            const empty = document.createElement('div');
            empty.className = 'activity-runtime-empty';
            empty.textContent = 'Belum ada aktivitas user terkait.';
            container.appendChild(empty);
            container.dataset.runtimePolished = 'done';
            return;
        }

        const list = document.createElement('div');
        list.className = 'activity-runtime-list';
        list.innerHTML = activities.map(cardHtml).join('');

        container.appendChild(list);
        container.dataset.runtimePolished = 'done';
    };

    const boot = () => {
        enhanceHistoryDom();

        const observer = new MutationObserver(() => {
            enhanceHistoryDom();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }
})();
