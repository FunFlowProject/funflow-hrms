/**
 * dashboard/admin-dashboard.js
 * Structured page controller for Admin Dashboard sections.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initAdminDashboardPage() {
    const pageNode = document.getElementById('admin-dashboard-page');

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
            '#hero-total-employees',
            '#hero-open-onboarding',
            '#hero-active-assignments',
            '#summary-total-employees',
            '#summary-active-workforce',
            '#summary-pending-employees',
            '#summary-terminated-employees',
            '#summary-active-assignments',
            '#summary-organization-nodes',
            '#summary-assignment-coverage',
            '#progress-joined-count',
            '#progress-pending-count',
            '#progress-onboarding-count',
            '#progress-terminated-count',
            '#progress-joined-percent',
            '#progress-pending-percent',
            '#progress-onboarding-percent',
            '#progress-terminated-percent',
        ],
        progressKeys: ['joined', 'pending', 'onboarding', 'terminated'],
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
            const totalEmployees = toNumber(stats.employees?.count);
            const pendingEmployees = toNumber(stats.pendingEmployees?.count);
            const onboardingEmployees = toNumber(stats.onboardingEmployees?.count);
            const joinedEmployees = toNumber(stats.joinedEmployees?.count);
            const terminatedEmployees = toNumber(stats.terminatedEmployees?.count);
            const subCompanies = toNumber(stats.subCompanies?.count);
            const squads = toNumber(stats.squads?.count);
            const activeAssignments = toNumber(stats.activeAssignments?.count);

            const activeWorkforce = joinedEmployees + onboardingEmployees;
            const openOnboarding = pendingEmployees + onboardingEmployees;
            const organizationNodes = subCompanies + squads;
            const assignmentCoverage = toPercent(activeAssignments, totalEmployees);

            const joinedPercent = toPercent(joinedEmployees, totalEmployees);
            const pendingPercent = toPercent(pendingEmployees, totalEmployees);
            const onboardingPercent = toPercent(onboardingEmployees, totalEmployees);
            const terminatedPercent = toPercent(terminatedEmployees, totalEmployees);

            Utils.setStatCards({
                '#hero-total-employees': totalEmployees,
                '#hero-open-onboarding': openOnboarding,
                '#hero-active-assignments': activeAssignments,
                '#summary-total-employees': totalEmployees,
                '#summary-active-workforce': activeWorkforce,
                '#summary-pending-employees': pendingEmployees,
                '#summary-active-assignments': activeAssignments,
                '#summary-organization-nodes': organizationNodes,
                '#summary-assignment-coverage': `${assignmentCoverage}%`,
                '#summary-terminated-employees': terminatedEmployees,
                '#progress-joined-count': joinedEmployees,
                '#progress-pending-count': pendingEmployees,
                '#progress-onboarding-count': onboardingEmployees,
                '#progress-terminated-count': terminatedEmployees,
                '#progress-joined-percent': `${joinedPercent}%`,
                '#progress-pending-percent': `${pendingPercent}%`,
                '#progress-onboarding-percent': `${onboardingPercent}%`,
                '#progress-terminated-percent': `${terminatedPercent}%`,
            });

            this.setProgressBar('joined', joinedPercent);
            this.setProgressBar('pending', pendingPercent);
            this.setProgressBar('onboarding', onboardingPercent);
            this.setProgressBar('terminated', terminatedPercent);
            this.setProgressEmpty(totalEmployees === 0);

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
