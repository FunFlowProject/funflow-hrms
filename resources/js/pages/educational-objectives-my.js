/**
 * educational-objectives/my-objectives.js
 * Structured page controller for Employee My Objectives view.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initMyObjectivesPage() {
    const pageNode = document.getElementById('my-objectives-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        objectives: Utils.createEndpoints(pageNode.dataset, {
            list: 'listUrl',
            stats: 'statsUrl',
            updateProgress: 'updateProgressUrlTemplate',
            complete: 'completeUrlTemplate',
        }),
    };

    const TRANSLATION = {
        error: {
            loadList: 'Unable to load objectives.',
            loadStats: 'Unable to load statistics.',
            updateProgress: 'Unable to save progress notes.',
            complete: 'Unable to mark objective as completed.',
        },
        success: {
            updateProgress: 'Progress notes updated.',
            complete: 'Objective formally completed.',
        },
        confirm: {
            complete: {
                titlePrefix: 'Complete ',
                confirmText: 'Yes, Complete',
            },
        },
    };

    const DOM = {
        grid: document.getElementById('objectives-grid'),
        loading: document.getElementById('objectives-loading'),
        empty: document.getElementById('objectives-empty'),
        searchForm: document.getElementById('my-objectives-search-form'),
        progressModal: document.getElementById('objectiveProgressModal'),
        progressForm: document.getElementById('objective-progress-form'),
    };

    if (!DOM.grid) {
        return;
    }

    const STATE = {
        statsSelectors: [
            '#summary-total',
            '#summary-not-started',
            '#summary-in-progress',
            '#summary-completed',
        ],
    };

    const ApiManager = {
        async fetchStats() {
            const response = await $.get(ROUTES.objectives.stats);
            return response?.data ?? {};
        },

        async fetchObjectives(search = '', status = '') {
            const response = await $.get(ROUTES.objectives.list, { search, status });
            return response?.data ?? [];
        },

        async updateProgress(id, formData) {
            return $.post(ROUTES.objectives.updateProgress(id), formData);
        },

        async complete(id) {
            return $.post(ROUTES.objectives.complete(id));
        },
    };

    const StatsManager = {
        setLoading(isLoading) {
            Utils.setStatsLoading({
                valueSelectors: STATE.statsSelectors,
                isLoading,
            });
        },

        setCards(stats = {}) {
            Utils.setStatCards({
                '#summary-total': stats.total ?? 0,
                '#summary-not-started': stats.not_started ?? 0,
                '#summary-in-progress': stats.in_progress ?? 0,
                '#summary-completed': stats.completed ?? 0,
            });
        },

        async refresh() {
            this.setLoading(true);

            try {
                const stats = await ApiManager.fetchStats();
                this.setCards(stats);
            } catch (error) {
                this.setCards({});
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadStats);
            } finally {
                this.setLoading(false);
            }
        },
    };

    const CardsManager = {
        _buildCardHtml(doc) {
            let priorityBadge = '';
            if (doc.priority === 'high') priorityBadge = '<span class="badge bg-danger rounded-pill px-3">High Priority</span>';
            if (doc.priority === 'medium') priorityBadge = '<span class="badge bg-warning rounded-pill px-3">Med Priority</span>';
            if (doc.priority === 'low') priorityBadge = '<span class="badge bg-info rounded-pill px-3">Low Priority</span>';

            let statusBadge = '';
            if (doc.employee_status.status === 'not_started') {
                statusBadge = `<span class="badge bg-secondary rounded px-2">Not Started</span>`;
            } else if (doc.employee_status.status === 'in_progress') {
                statusBadge = `<span class="badge bg-info rounded px-2">In Progress</span>`;
            } else {
                statusBadge = `<span class="badge bg-success rounded px-2"><i class="bx bx-check"></i> ${doc.employee_status.status_label}</span>`;
            }

            const downloadLink = doc.attachment ? (doc.attachment.startsWith('http') ? doc.attachment : `/storage/${doc.attachment}`) : null;
            const btnText = doc.attachment ? (doc.attachment.startsWith('http') ? '<i class="bx bx-link-external me-1"></i> Open Resource' : '<i class="bx bx-download me-1"></i> Download File') : '';

            let actionsHtml = '';
            if (doc.employee_status.status !== 'completed') {
                actionsHtml = `
                    <div class="mt-3 d-flex gap-2 w-100">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill btn-progress" data-id="${doc.id}" data-notes="${doc.employee_status.progress_notes ?? ''}">
                            <i class="bx bx-pencil me-1"></i> Update
                        </button>
                        <button type="button" class="btn btn-success btn-sm flex-fill btn-complete" data-id="${doc.id}" data-name="${doc.name}">
                            <i class="bx bx-check-circle me-1"></i> Complete
                        </button>
                    </div>
                `;
            }

            return `
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm transition-hover ${doc.mandatory ? 'border-start border-4 border-warning' : ''}">
                        <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-start">
                            ${priorityBadge}
                            ${statusBadge}
                        </div>
                        <div class="card-body pb-0">
                            <h5 class="card-title fw-bold text-dark mb-2">${doc.name}</h5>
                            <p class="small text-muted mb-3" style="min-height: 2.5rem;">${doc.description ?? 'No description provided.'}</p>
                            
                            <div class="d-flex justify-content-between text-muted small mb-2">
                                <span><i class="bx bx-calendar me-1"></i> Target: ${doc.target_date ?? 'No strict deadline'}</span>
                                ${doc.mandatory ? '<span class="text-warning fw-bold"><i class="bx bxs-star me-1"></i> Mandatory</span>' : ''}
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-4 pt-3 d-flex flex-column align-items-start">
                            ${downloadLink ? `<a href="${downloadLink}" target="_blank" class="btn btn-label-primary btn-sm w-100 mb-2">${btnText}</a>` : ''}
                            
                            ${doc.employee_status.progress_notes ? `
                                <div class="bg-light p-2 rounded w-100 text-muted small mb-0 mt-2 text-wrap text-break">
                                    <strong><i class="bx bx-message-square-detail"></i> My Notes:</strong><br/>
                                    ${doc.employee_status.progress_notes}
                                </div>
                            ` : ''}

                            ${actionsHtml}
                        </div>
                    </div>
                </div>
            `;
        },

        async loadCards() {
            DOM.loading.classList.remove('d-none');
            DOM.grid.classList.add('d-none');
            DOM.empty.classList.add('d-none');
            
            const search = $('#filter-objective-name').val() ?? '';
            const status = $('#filter-objective-status').val() ?? '';

            try {
                const objectives = await ApiManager.fetchObjectives(search, status);
                
                if (objectives.length === 0) {
                    DOM.empty.classList.remove('d-none');
                    return;
                }

                const html = objectives.map(doc => this._buildCardHtml(doc)).join('');
                DOM.grid.innerHTML = html;
                DOM.grid.classList.remove('d-none');
            } catch (error) {
                Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadList);
                DOM.empty.classList.remove('d-none');
            } finally {
                DOM.loading.classList.add('d-none');
            }
        }
    };

    const UIRefreshManager = {
        async refreshAll() {
            await CardsManager.loadCards();
            await StatsManager.refresh();
        },
    };

    const FilterManager = {
        init() {
            Utils.initSelect2('.select2-filter');

            $('#my-objectives-search-form').on('submit', (e) => {
                e.preventDefault();
                UIRefreshManager.refreshAll();
            });

            $('#btn-reset-objective-filters').on('click', () => {
                $('#filter-objective-name').val('');
                Utils.clearSelect2('#filter-objective-status');
                UIRefreshManager.refreshAll();
            });
        },
    };

    const ActionsManager = {
        init() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Handle Progress Update Click
            $(document).on('click', '.btn-progress', function() {
                const id = $(this).data('id');
                const notes = $(this).data('notes') || '';
                
                $('#progress_objective_id').val(id);
                $('#form-objective-progress-notes').val(notes);
                
                const bsModal = new bootstrap.Modal(DOM.progressModal);
                bsModal.show();
            });

            // Handle Completion
            $(document).on('click', '.btn-complete', async function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                if (!id) return;

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.complete.titlePrefix}${name}?`,
                    confirmText: TRANSLATION.confirm.complete.confirmText,
                    icon: 'success'
                });

                if (!confirmed) return;

                const $btn = $(this);
                Utils.setBtnLoading($btn, true, 'Completing...');

                try {
                    const response = await ApiManager.complete(id);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.complete);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.complete);
                } finally {
                    Utils.setBtnLoading($btn, false);
                }
            });

            // Handle Progress Form Submission
            $(DOM.progressForm).on('submit', async function(e) {
                e.preventDefault();
                
                const $submitBtn = $(this).find('button[type="submit"]');
                Utils.clearFormErrors('#objective-progress-form-errors');
                
                const id = $('#progress_objective_id').val();
                const formData = $(this).serialize();

                Utils.setBtnLoading($submitBtn, true);

                try {
                    const response = await ApiManager.updateProgress(id, formData);
                    bootstrap.Modal.getInstance(DOM.progressModal).hide();
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.updateProgress);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    if (error.status === 422 && error.responseJSON?.errors) {
                        Utils.showFormErrors('#objective-progress-form-errors', error.responseJSON.errors);
                        return;
                    }
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.updateProgress);
                } finally {
                    Utils.setBtnLoading($submitBtn, false);
                }
            });

            DOM.progressModal.addEventListener('hidden.bs.modal', () => {
                Utils.clearFormErrors('#objective-progress-form-errors');
                DOM.progressForm.reset();
            });
        }
    };

    const MyObjectivesApp = {
        initEvents() {
            FilterManager.init();
            ActionsManager.init();
        },

        initData() {
            UIRefreshManager.refreshAll();
        },

        init() {
            this.initEvents();
            this.initData();
        },
    };

    MyObjectivesApp.init();
}
