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

/* Kicap PWA polish: klik luar popup detail peserta untuk tutup */
(() => {
    'use strict';

    if (window.__kicapParticipantPopupOutsideCloseInstalled) {
        return;
    }

    window.__kicapParticipantPopupOutsideCloseInstalled = true;

    const isVisible = (element) => {
        if (!element) return false;

        const style = window.getComputedStyle(element);
        const rect = element.getBoundingClientRect();

        return style.display !== 'none'
            && style.visibility !== 'hidden'
            && Number(style.opacity || 1) !== 0
            && rect.width > 0
            && rect.height > 0;
    };

    const findVisibleCloseButton = () => {
        const buttons = Array.from(document.querySelectorAll('button'));

        return buttons.find((button) => {
            const text = (button.textContent || '').trim().toLowerCase();

            return isVisible(button)
                && (
                    button.classList.contains('sheet-close-button')
                    || text === 'tutup'
                    || text.includes('tutup')
                );
        }) || null;
    };

    const findPopupPanel = (closeButton) => {
        if (!closeButton) return null;

        return closeButton.closest([
            '.participant-detail-preview',
            '.participant-detail-sheet',
            '.participant-sheet',
            '.participant-sheet-panel',
            '.sheet-panel',
            '.sheet-content',
            '.selection-participant-detail',
            '.selection-participant-sheet',
            '.photo-lightbox-panel'
        ].join(', ')) || closeButton.closest('section, article, div');
    };

    const shouldIgnore = (target) => {
        if (!target || !(target instanceof Element)) return true;

        return Boolean(target.closest([
            '.bottom-nav',
            '.photo-lightbox-panel',
            '.photo-lightbox-close',
            '.participant-photo-lightbox',
            '.participant-photo',
            '.participant-sheet-photo',
            '.participant-photo-source-field',
            '.participant-photo-source-actions',
            'input',
            'select',
            'textarea',
            'label'
        ].join(', ')));
    };

    document.addEventListener('click', (event) => {
        const target = event.target;

        if (!(target instanceof Element)) return;
        if (shouldIgnore(target)) return;

        const closeButton = findVisibleCloseButton();

        if (!closeButton) return;

        const panel = findPopupPanel(closeButton);

        if (!panel || !isVisible(panel)) return;

        if (panel.contains(target)) return;

        event.preventDefault();
        event.stopPropagation();

        closeButton.click();
    }, true);
})();


// KICAP_PARTICIPANT_POPUP_FLOATING_CLOSE_START
(() => {
    'use strict';

    const MARKER_ID = 'kicap-floating-participant-close';

    const isVisible = (element) => {
        if (!element) return false;

        const style = window.getComputedStyle(element);
        const rect = element.getBoundingClientRect();

        return style.display !== 'none'
            && style.visibility !== 'hidden'
            && Number(style.opacity || 1) !== 0
            && rect.width > 0
            && rect.height > 0;
    };

    const isParticipantDetailOpen = () => {
        const texts = Array.from(document.querySelectorAll('h1,h2,h3,strong,small,span,div'))
            .slice(0, 800)
            .map((node) => (node.textContent || '').trim().toLowerCase());

        return texts.some((text) => text === 'detail peserta' || text.includes('detail peserta'));
    };

    const findCloseButton = () => {
        const buttons = Array.from(document.querySelectorAll('button'));

        return buttons.find((button) => {
            const text = (button.textContent || '').trim().toLowerCase();

            return isVisible(button)
                && (
                    button.classList.contains('sheet-close-button')
                    || text === 'tutup'
                    || text.includes('tutup')
                );
        }) || null;
    };

    const closeParticipantDetail = () => {
        const closeButton = findCloseButton();

        if (closeButton) {
            closeButton.click();
            return true;
        }

        document.dispatchEvent(new KeyboardEvent('keydown', {
            key: 'Escape',
            code: 'Escape',
            bubbles: true,
        }));

        return false;
    };

    const ensureFloatingButton = () => {
        let button = document.getElementById(MARKER_ID);

        if (!button) {
            button = document.createElement('button');
            button.id = MARKER_ID;
            button.type = 'button';
            button.className = 'kicap-floating-participant-close';
            button.setAttribute('aria-label', 'Tutup detail peserta');
            button.innerHTML = '×';

            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                closeParticipantDetail();
            }, true);

            document.body.appendChild(button);
        }

        return button;
    };

    const findLikelyPanel = () => {
        const closeButton = findCloseButton();

        if (!closeButton) return null;

        return closeButton.closest([
            '.participant-detail-preview',
            '.participant-detail-sheet',
            '.participant-sheet',
            '.sheet-panel',
            '.sheet-content',
            '.selection-panel',
            '.selection-participant-sheet'
        ].join(', '));
    };

    const refreshFloatingClose = () => {
        const floatingButton = ensureFloatingButton();
        const shouldShow = isParticipantDetailOpen() && Boolean(findCloseButton());

        floatingButton.classList.toggle('is-visible', shouldShow);
    };

    document.addEventListener('pointerdown', (event) => {
        const target = event.target;

        if (!(target instanceof Element)) return;

        const floatingButton = document.getElementById(MARKER_ID);

        if (!floatingButton || !floatingButton.classList.contains('is-visible')) return;

        if (target.closest('#' + MARKER_ID)) return;
        if (target.closest('input, textarea, select, label')) return;
        if (target.closest('.photo-lightbox-panel, .participant-photo-lightbox')) return;

        const panel = findLikelyPanel();

        if (panel && panel.contains(target)) return;

        event.preventDefault();
        event.stopPropagation();

        closeParticipantDetail();
    }, true);

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isParticipantDetailOpen()) {
            closeParticipantDetail();
        }
    }, true);

    const observer = new MutationObserver(() => {
        window.requestAnimationFrame(refreshFloatingClose);
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style'],
    });

    window.addEventListener('scroll', refreshFloatingClose, true);
    window.addEventListener('resize', refreshFloatingClose);

    refreshFloatingClose();
})();
// KICAP_PARTICIPANT_POPUP_FLOATING_CLOSE_END
