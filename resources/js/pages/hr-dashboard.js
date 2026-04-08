/**
 * dashboard/hr-dashboard.js
 * Structured page controller for HR Dashboard sections.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initHrDashboardPage() {
    const pageNode = document.getElementById('hr-dashboard-page');

    if (!pageNode || !window.$) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        stats: pageNode.dataset.statsUrl,
    };

    const STATE = {
        statsSelectors: [
            '#hero-hr-open-onboarding',
            '#hero-hr-unassigned-queue',
            '#hero-hr-my-queue',
            '#summary-hr-pending-employees',
            '#summary-hr-onboarding-employees',
            '#summary-hr-active-assignments',
            '#summary-hr-submitted-requests',
            '#summary-hr-in-progress-requests',
            '#summary-hr-unassigned-requests',
            '#progress-submitted-count',
            '#progress-in-progress-count',
            '#progress-completed-count',
            '#progress-rejected-count',
            '#progress-submitted-percent',
            '#progress-in-progress-percent',
            '#progress-completed-percent',
            '#progress-rejected-percent',
        ],
        progressKeys: ['submitted', 'in-progress', 'completed', 'rejected'],
    };

    const toNumber = (value) => {
        const parsed = Number(value);
        return Number.isFinite(parsed) ? parsed : 0;
    };

    const toPercent = (value, total) => {
        if (total <= 0) {
            return 0;
        }

        const percentage = Math.round((value / total) * 100);
        return Math.min(100, Math.max(0, percentage));
    };

    const ApiManager = {
        async fetchStats() {
            const response = await $.get(ROUTES.stats);

            return response?.data ?? {};
        },
    };

    const ViewManager = {
        setProgressBar(key, percent) {
            const barNode = document.getElementById(`progress-${key}-bar`);

            if (!barNode) {
                return;
            }

            const safePercent = Number.isFinite(percent) ? percent : 0;
            barNode.style.width = `${safePercent}%`;
            barNode.setAttribute('aria-valuenow', String(safePercent));
        },

        setProgressEmpty(isEmpty) {
            const progressContent = document.getElementById('dashboard-progress-content');
            const progressEmpty = document.getElementById('dashboard-progress-empty');

            if (progressContent) {
                progressContent.classList.toggle('d-none', isEmpty);
            }

            if (progressEmpty) {
                progressEmpty.classList.toggle('d-none', !isEmpty);
            }
        },

        setLoading(isLoading) {
            Utils.setStatsLoading({
                valueSelectors: STATE.statsSelectors,
                isLoading,
            });

            if (isLoading) {
                STATE.progressKeys.forEach((key) => this.setProgressBar(key, 0));
            }
        },

        setCards(stats = {}) {
            const pendingEmployees = toNumber(stats.pendingEmployees?.count);
            const onboardingEmployees = toNumber(stats.onboardingEmployees?.count);
            const activeAssignments = toNumber(stats.activeAssignments?.count);
            const submittedRequests = toNumber(stats.submittedRequests?.count);
            const inProgressRequests = toNumber(stats.inProgressRequests?.count);
            const completedRequests = toNumber(stats.completedRequests?.count);
            const rejectedRequests = toNumber(stats.rejectedRequests?.count);
            const unassignedOpenRequests = toNumber(stats.unassignedOpenRequests?.count);
            const myOpenRequests = toNumber(stats.myOpenRequests?.count);

            const openOnboarding = pendingEmployees + onboardingEmployees;
            const totalRequests = submittedRequests + inProgressRequests + completedRequests + rejectedRequests;

            const submittedPercent = toPercent(submittedRequests, totalRequests);
            const inProgressPercent = toPercent(inProgressRequests, totalRequests);
            const completedPercent = toPercent(completedRequests, totalRequests);
            const rejectedPercent = toPercent(rejectedRequests, totalRequests);

            Utils.setStatCards({
                '#hero-hr-open-onboarding': openOnboarding,
                '#hero-hr-unassigned-queue': unassignedOpenRequests,
                '#hero-hr-my-queue': myOpenRequests,
                '#summary-hr-pending-employees': pendingEmployees,
                '#summary-hr-onboarding-employees': onboardingEmployees,
                '#summary-hr-active-assignments': activeAssignments,
                '#summary-hr-submitted-requests': submittedRequests,
                '#summary-hr-in-progress-requests': inProgressRequests,
                '#summary-hr-unassigned-requests': unassignedOpenRequests,
                '#progress-submitted-count': submittedRequests,
                '#progress-in-progress-count': inProgressRequests,
                '#progress-completed-count': completedRequests,
                '#progress-rejected-count': rejectedRequests,
                '#progress-submitted-percent': `${submittedPercent}%`,
                '#progress-in-progress-percent': `${inProgressPercent}%`,
                '#progress-completed-percent': `${completedPercent}%`,
                '#progress-rejected-percent': `${rejectedPercent}%`,
            });

            this.setProgressBar('submitted', submittedPercent);
            this.setProgressBar('in-progress', inProgressPercent);
            this.setProgressBar('completed', completedPercent);
            this.setProgressBar('rejected', rejectedPercent);
            this.setProgressEmpty(totalRequests === 0);

            Utils.setLastUpdated(stats.dashboard?.lastUpdateTime ?? null);
        },
    };

    const DashboardManager = {
        async refresh() {
            ViewManager.setLoading(true);

            try {
                const stats = await ApiManager.fetchStats();
                ViewManager.setCards(stats);
            } catch (error) {
                ViewManager.setCards({});
                Utils.showAlert('danger', error.responseJSON?.message ?? 'Unable to load dashboard statistics.');
            } finally {
                ViewManager.setLoading(false);
            }
        },
    };

    void DashboardManager.refresh();
}
