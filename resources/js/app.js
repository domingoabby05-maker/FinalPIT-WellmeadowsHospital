import './bootstrap';

const applyTheme = (theme) => {
    const isDark = theme === 'dark';

    document.body.classList.toggle('theme-dark', isDark);
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', String(isDark));
    });
    document.querySelectorAll('[data-theme-toggle-icon]').forEach((node) => {
        node.textContent = isDark ? 'Night' : 'Light';
    });
    document.querySelectorAll('[data-theme-toggle-label]').forEach((node) => {
        node.textContent = isDark ? 'Night Mode' : 'Light Mode';
    });
};

const savedTheme = localStorage.getItem('wellmeadows-theme') || 'light';
applyTheme(savedTheme);

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const nextTheme = document.body.classList.contains('theme-dark') ? 'light' : 'dark';
        localStorage.setItem('wellmeadows-theme', nextTheme);
        applyTheme(nextTheme);
    });
});

const showScreen = () => {
    const target = (location.hash || '#dashboard').replace('#', '');
    const section = target.startsWith('ward-detail-') ? 'wards' : target;
    const selectedWard = target.startsWith('ward-detail-') ? target.replace('ward-detail-', '') : null;
    document.querySelectorAll('.screen').forEach((screen) => {
        screen.classList.toggle('active', screen.id === `${section}-screen`);
    });
    document.querySelectorAll('[data-nav]').forEach((link) => {
        link.classList.toggle('active', link.dataset.nav === section);
    });
    document.querySelectorAll('[data-ward-card]').forEach((card) => {
        card.classList.toggle('is-highlighted', card.dataset.wardCard === selectedWard);
    });
    document.querySelectorAll('[data-ward-detail]').forEach((detail) => {
        detail.classList.toggle('is-highlighted', detail.dataset.wardDetail === selectedWard);
    });

    if (target.startsWith('ward-detail-')) {
        setTimeout(() => document.getElementById(target)?.scrollIntoView({ behavior: 'smooth', block: 'start' }), 0);
    }
};

if (document.body.dataset.authenticated === 'true') {
    if (!location.hash || location.hash === '#signin') {
        location.hash = '#dashboard';
    }
    document.body.classList.add('app-ready');
}

showScreen();
window.addEventListener('hashchange', showScreen);

document.querySelectorAll('[data-modal]').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById(button.dataset.modal)?.showModal();
    });
});

document.querySelectorAll('[data-close]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
});

const updateTableFilter = (tableId) => {
    const table = document.getElementById(tableId);
    const search = document.querySelector(`[data-search="${tableId}"]`);
    const filter = document.querySelector(`[data-table-filter="${tableId}"]`);
    const needle = search?.value.trim().toLowerCase() || '';
    const selected = filter?.value.trim().toLowerCase() || '';
    const column = filter?.dataset.filterColumn || '';

    table?.querySelectorAll('tbody tr').forEach((row) => {
        const matchesSearch = row.textContent.toLowerCase().includes(needle);
        const matchesFilter = !selected || row.dataset[column] === selected;

        row.hidden = !(matchesSearch && matchesFilter);
    });
};

document.querySelectorAll('[data-search]').forEach((input) => {
    input.addEventListener('input', () => updateTableFilter(input.dataset.search));
});

document.querySelectorAll('[data-table-search-button]').forEach((button) => {
    button.addEventListener('click', () => updateTableFilter(button.dataset.tableSearchButton));
});

document.querySelectorAll('[data-table-filter]').forEach((select) => {
    select.addEventListener('change', () => updateTableFilter(select.dataset.tableFilter));
    updateTableFilter(select.dataset.tableFilter);
});

const updateLocalDoctorFields = (input) => {
    const form = input.closest('form');
    const datalist = input.list;
    const selected = [...(datalist?.options || [])].find((option) => option.value === input.value);

    if (!form || !selected) {
        return;
    }

    form.querySelector('[name="doctor_full_name"]').value = selected.dataset.name || '';
    form.querySelector('[name="clinic_number"]').value = selected.dataset.clinic || '';
    form.querySelector('[name="doctor_address"]').value = selected.dataset.address || '';
    form.querySelector('[name="doctor_phone"]').value = selected.dataset.phone || '';
};

document.querySelectorAll('[data-local-doctor-input]').forEach((input) => {
    input.addEventListener('change', () => updateLocalDoctorFields(input));
    input.addEventListener('input', () => updateLocalDoctorFields(input));
});

const updateBedOptions = (wardSelect) => {
    const form = wardSelect.closest('form');
    const bedSelect = form?.querySelector('[data-bed-select]');
    const selectedWard = wardSelect.value;

    if (!bedSelect) {
        return;
    }

    [...bedSelect.options].forEach((option) => {
        if (!option.value) {
            option.hidden = false;
            return;
        }

        option.hidden = Boolean(selectedWard) && option.dataset.wardId !== selectedWard;
    });

    if (bedSelect.selectedOptions[0]?.hidden) {
        bedSelect.value = '';
    }
};

document.querySelectorAll('form select[name="ward_id"], form [data-ward-select]').forEach((select) => {
    select.addEventListener('change', () => updateBedOptions(select));
    updateBedOptions(select);
});

document.querySelectorAll('[data-card-search]').forEach((input) => {
    const updateCardSearch = () => {
        const needle = input.value.trim().toLowerCase();
        const grid = document.querySelector(`.${input.dataset.cardSearch}`);
        const cards = grid ? [...grid.querySelectorAll('article, a')] : [];
        let visible = 0;

        cards.forEach((card) => {
            const haystack = (card.dataset.searchable || card.textContent).toLowerCase();
            const matches = haystack.includes(needle);

            card.hidden = !matches;
            if (matches) {
                visible += 1;
            }
        });

        const results = input.dataset.resultsTarget ? document.getElementById(input.dataset.resultsTarget) : null;
        if (results) {
            const singular = input.dataset.resultLabelSingular || 'staff record';
            const plural = input.dataset.resultLabelPlural || 'staff records';
            const noun = visible === 1 ? singular : plural;
            results.textContent = needle ? `${visible} ${noun} found` : `${visible} ${noun} shown`;
        }

        const empty = input.dataset.emptyTarget ? document.getElementById(input.dataset.emptyTarget) : null;
        if (empty) {
            empty.hidden = visible > 0;
        }
    };

    input.addEventListener('input', () => {
        updateCardSearch();
    });

    updateCardSearch();
});

document.querySelectorAll('[data-search-button]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.querySelector(`[data-card-search="${button.dataset.searchButton}"]`);
        input?.dispatchEvent(new Event('input', { bubbles: true }));
        input?.focus();
    });
});

const showAuthForm = (name) => {
    document.querySelectorAll('[data-auth-tab]').forEach((button) => {
        button.classList.toggle('active', button.dataset.authTab === name);
    });
    document.querySelectorAll('[data-auth-form]').forEach((form) => {
        form.classList.toggle('active', form.dataset.authForm === name);
    });
};

document.querySelectorAll('[data-auth-tab]').forEach((button) => {
    button.addEventListener('click', () => showAuthForm(button.dataset.authTab));
});

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

const updateText = (selector, value) => {
    const node = document.querySelector(selector);
    if (node) {
        node.textContent = value;
    }
};

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');

const activityMarkup = (activity) => `
    <article>
        <time>${escapeHtml(activity.date_label)}</time>
        <strong>${escapeHtml(activity.title)}</strong>
        <span>${escapeHtml(activity.detail)}</span>
        <small>${escapeHtml(activity.type)} - ${escapeHtml(activity.time_label)}</small>
    </article>
`;

const refreshDashboard = async () => {
    const url = document.body.dataset.dashboardLiveUrl;

    if (!url || document.body.dataset.authenticated !== 'true') {
        return;
    }

    try {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            return;
        }

        const payload = await response.json();
        const stats = payload.stats ?? {};

        updateText('[data-stat="patients"]', stats.patients ?? 0);
        updateText('[data-stat="occupied_beds"]', `${stats.occupied_beds ?? 0}/${stats.total_beds ?? 0}`);
        updateText('[data-stat="occupancy"]', `${stats.occupancy ?? 0}%`);
        updateText('[data-stat="today_appointments"]', stats.today_appointments ?? 0);
        updateText('[data-stat="staff"]', stats.staff ?? 0);
        updateText('[data-stat-label="patients"]', `${stats.patients ?? 0} patients`);
        updateText('[data-stat-label="staff"]', `${stats.staff ?? 0} staff members`);
        updateText('[data-stat-label="today_appointments"]', `${stats.today_appointments ?? 0} today`);
        updateText('[data-stat-label="active_prescriptions"]', `${stats.active_prescriptions ?? 0} active`);
        updateText('[data-live-updated]', payload.generated_at ?? '');

        if (Array.isArray(payload.wards)) {
            payload.wards.forEach((ward) => {
                const progress = document.querySelector(`[data-ward-progress="${ward.ward_id}"]`);
                const rate = ward.total_beds > 0 ? Math.round((ward.occupied_beds / ward.total_beds) * 100) : 0;

                updateText(`[data-ward-occupancy="${ward.ward_id}"]`, `${ward.occupied_beds}/${ward.total_beds}`);
                updateText(`[data-ward-available="${ward.ward_id}"]`, ward.available_beds ?? 0);
                updateText(`[data-ward-patients="${ward.ward_id}"]`, ward.occupied_beds ?? 0);
                updateText(`[data-ward-staff="${ward.ward_id}"]`, ward.staff_count ?? 0);
                if (progress) {
                    progress.style.width = `${rate}%`;
                }
            });
        }

        const activity = document.querySelector('[data-live-activity]');
        if (activity && Array.isArray(payload.recent_activities)) {
            activity.innerHTML = payload.recent_activities.map(activityMarkup).join('');
        }
    } catch {
        // The dashboard keeps the last known data if a refresh fails.
    }
};

if (document.querySelector('[data-live-activity]')) {
    setInterval(refreshDashboard, 15000);
}
