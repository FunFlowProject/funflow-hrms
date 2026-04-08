/**
 * dashboard/employee-dashboard.js
 * Structured page controller for Employee Dashboard sections.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initEmployeeDashboardPage() {
    const pageNode = document.getElementById('employee-dashboard-page');

    if (!pageNode || !window.$) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        stats: pageNode.dataset.statsUrl,
    };

    const DOM = {
        profileFullName: document.getElementById('profile-full-name'),
        profileEmail: document.getElementById('profile-email'),
        profilePosition: document.getElementById('profile-position'),
        profileSubCompany: document.getElementById('profile-sub-company'),
        profileSquad: document.getElementById('profile-squad'),
        profileCeo: document.getElementById('profile-ceo'),
        profileLeader: document.getElementById('profile-leader'),
        profileUpperPositions: document.getElementById('profile-upper-positions'),
        profileUpperPositionsEmpty: document.getElementById('profile-upper-positions-empty'),
    };

    const STATE = {
        statsSelectors: [
            '#hero-employee-open-requests',
            '#hero-employee-active-assignments',
            '#hero-employee-completed-requests',
            '#summary-employee-submitted',
            '#summary-employee-in-progress',
            '#summary-employee-completed',
            '#summary-employee-rejected',
            '#summary-employee-active-assignments',
            '#summary-employee-total-assignments',
        ],
    };

    const toNumber = (value) => {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const formatLeader = (leader) => {
        if (!leader || typeof leader !== 'object') {
            return '-';
        }

        const name = String(leader.name ?? '').trim();
        const position = String(leader.position ?? '').trim();

        if (!name && !position) {
            return '-';
        }

        if (!name) {
            return position;
        }

        if (!position) {
            return name;
        }

        return `${name} (${position})`;
    };

    const ApiManager = {
        async fetchStats() {
            const response = await $.get(ROUTES.stats);

            return response?.data ?? {};
        },
    };

    const ViewManager = {
        setLoading(isLoading) {
            Utils.setStatsLoading({
                valueSelectors: STATE.statsSelectors,
                isLoading,
            });
        },

        setCards(stats = {}) {
            const submittedRequests = toNumber(stats.submittedRequests?.count);
            const inProgressRequests = toNumber(stats.inProgressRequests?.count);
            const completedRequests = toNumber(stats.completedRequests?.count);
            const rejectedRequests = toNumber(stats.rejectedRequests?.count);
            const activeAssignments = toNumber(stats.activeAssignments?.count);
            const totalAssignments = toNumber(stats.totalAssignments?.count);

            const openRequests = submittedRequests + inProgressRequests;

            Utils.setStatCards({
                '#hero-employee-open-requests': openRequests,
                '#hero-employee-active-assignments': activeAssignments,
                '#hero-employee-completed-requests': completedRequests,
                '#summary-employee-submitted': submittedRequests,
                '#summary-employee-in-progress': inProgressRequests,
                '#summary-employee-completed': completedRequests,
                '#summary-employee-rejected': rejectedRequests,
                '#summary-employee-active-assignments': activeAssignments,
                '#summary-employee-total-assignments': totalAssignments,
            });

            Utils.setLastUpdated(stats.dashboard?.lastUpdateTime ?? null);
        },

        setProfile(profile = {}, leadership = {}) {
            if (DOM.profileFullName) {
                DOM.profileFullName.textContent = String(profile.fullName ?? '-');
            }

            if (DOM.profileEmail) {
                DOM.profileEmail.textContent = String(profile.email ?? '-');
            }

            if (DOM.profilePosition) {
                DOM.profilePosition.textContent = String(profile.position ?? '-');
            }

            if (DOM.profileSubCompany) {
                DOM.profileSubCompany.textContent = String(profile.subCompany ?? '-');
            }

            if (DOM.profileSquad) {
                DOM.profileSquad.textContent = String(profile.squad ?? '-');
            }

            if (DOM.profileCeo) {
                DOM.profileCeo.textContent = formatLeader(leadership.ceo);
            }

            if (DOM.profileLeader) {
                DOM.profileLeader.textContent = formatLeader(leadership.leader);
            }

            if (!DOM.profileUpperPositions || !DOM.profileUpperPositionsEmpty) {
                return;
            }

            const upperPositions = Array.isArray(leadership.upperPositions) ? leadership.upperPositions : [];
            DOM.profileUpperPositions.innerHTML = '';

            if (upperPositions.length === 0) {
                DOM.profileUpperPositionsEmpty.classList.remove('d-none');
                DOM.profileUpperPositions.classList.add('d-none');
                return;
            }

            const rows = upperPositions.map((item) => {
                const name = String(item.name ?? '-');
                const position = String(item.position ?? '-');
                const scope = String(item.scope ?? '-');

                return `<li class="list-group-item px-0 py-2 border-0 border-bottom">
                    <div class="fw-semibold text-dark">${position}</div>
                    <small class="text-secondary">${name} · ${scope}</small>
                </li>`;
            });

            DOM.profileUpperPositions.innerHTML = rows.join('');
            DOM.profileUpperPositionsEmpty.classList.add('d-none');
            DOM.profileUpperPositions.classList.remove('d-none');
        },
    };

    const DashboardManager = {
        async refresh() {
            ViewManager.setLoading(true);

            try {
                const stats = await ApiManager.fetchStats();
                ViewManager.setCards(stats);
                ViewManager.setProfile(stats.profile ?? {}, stats.leadership ?? {});
            } catch (error) {
                ViewManager.setCards({});
                ViewManager.setProfile({}, {});
                Utils.showAlert('danger', error.responseJSON?.message ?? 'Unable to load dashboard statistics.');
            } finally {
                ViewManager.setLoading(false);
            }
        },
    };

    void DashboardManager.refresh();
}
