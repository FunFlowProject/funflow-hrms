/**
 * documents/my-documents.js
 * Structured page controller for Employee My Documents view.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initMyDocumentsPage() {
    const pageNode = document.getElementById('my-documents-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        myDocuments: Utils.createEndpoints(pageNode.dataset, {
            list: 'listUrl',
            stats: 'statsUrl',
            markViewed: 'markViewedUrlTemplate',
            acknowledge: 'acknowledgeUrlTemplate',
        }),
    };

    const TRANSLATION = {
        error: {
            loadList: 'Unable to load documents.',
            loadStats: 'Unable to load statistics.',
            markViewed: 'Unable to mark document as viewed.',
            acknowledge: 'Unable to acknowledge document.',
        },
        success: {
            markViewed: 'Document marked as viewed.',
            acknowledge: 'Document formally acknowledged.',
        },
    };

    const DOM = {
        grid: document.getElementById('documents-grid'),
        loading: document.getElementById('documents-loading'),
        empty: document.getElementById('documents-empty'),
        searchForm: document.getElementById('my-documents-search-form'),
    };

    if (!DOM.grid) {
        return;
    }

    const STATE = {
        statsSelectors: [
            '#summary-total',
            '#summary-new',
            '#summary-requires-ack',
            '#summary-acknowledged',
        ],
    };

    const ApiManager = {
        async fetchStats() {
            const response = await $.get(ROUTES.myDocuments.stats);
            return response?.data ?? {};
        },

        async fetchDocuments(search = '', classification = '') {
            const response = await $.get(ROUTES.myDocuments.list, { search, classification });
            return response?.data ?? [];
        },

        async markViewed(docId) {
            return $.post(ROUTES.myDocuments.markViewed(docId));
        },

        async acknowledge(docId) {
            return $.post(ROUTES.myDocuments.acknowledge(docId));
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
                '#summary-new': stats.new ?? 0,
                '#summary-requires-ack': stats.requires_ack ?? 0,
                '#summary-acknowledged': stats.acknowledged ?? 0,
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
            const classColor = doc.classification === 'confidential' ? 'bg-danger' : 
                               (doc.classification === 'public' ? 'bg-success' : 'bg-info');
            
            let statusBadge = '';
            if (doc.employee_status.status === 'new') {
                statusBadge = `<span class="badge bg-warning rounded-pill px-3">New</span>`;
            } else if (doc.employee_status.status === 'viewed' && doc.requires_acknowledgment) {
                statusBadge = `<span class="badge bg-danger rounded-pill px-3">Requires Acknowledgment</span>`;
            } else {
                statusBadge = `<span class="badge bg-success rounded-pill px-3"><i class="bx bx-check"></i> ${doc.employee_status.status_label}</span>`;
            }

            const downloadLink = doc.file_type === 'url' ? doc.file_path : `/storage/${doc.file_path}`;
            const btnText = doc.file_type === 'url' ? '<i class="bx bx-link-external me-1"></i> Open Link' : '<i class="bx bx-download me-1"></i> Download File';

            let ackBtn = '';
            if (doc.requires_acknowledgment && doc.employee_status.status !== 'acknowledged') {
                ackBtn = `
                    <button type="button" class="btn btn-primary btn-sm w-100 mt-2 btn-acknowledge" data-id="${doc.id}">
                        <i class="bx bx-check-circle me-1"></i> Acknowledge
                    </button>
                `;
            }

            return `
                <div class="col-md-6 col-xl-4">
                    <div class="card h-100 border-0 shadow-sm transition-hover">
                        <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-start">
                            <span class="badge ${classColor} mb-2 text-wrap text-start" style="max-width: 50%;">
                                ${doc.classification_label}
                            </span>
                            ${statusBadge}
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-dark mb-3">${doc.name}</h5>
                            <p class="text-muted small mb-1">
                                <i class="bx bx-folder me-1"></i> ${doc.scope_type_label} ${doc.scope_name ? `(${doc.scope_name})` : ''}
                            </p>
                            <p class="text-muted small mb-0">
                                <i class="bx bx-calendar me-1"></i> Added on: ${doc.created_at}
                            </p>
                        </div>
                        <div class="card-footer bg-white border-top-0 pb-4 pt-0">
                            <a href="${downloadLink}" target="_blank" class="btn btn-outline-primary btn-sm w-100 btn-mark-viewed" data-id="${doc.id}">
                                ${btnText}
                            </a>
                            ${ackBtn}
                        </div>
                    </div>
                </div>
            `;
        },

        async loadCards() {
            DOM.loading.classList.remove('d-none');
            DOM.grid.classList.add('d-none');
            DOM.empty.classList.add('d-none');
            
            const search = $('#filter-document-name').val() ?? '';
            const classification = $('#filter-document-classification').val() ?? '';

            try {
                const documents = await ApiManager.fetchDocuments(search, classification);
                
                if (documents.length === 0) {
                    DOM.empty.classList.remove('d-none');
                    return;
                }

                const html = documents.map(doc => this._buildCardHtml(doc)).join('');
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

            // Handle standard submit instead of real-time for variety, or keep real-time by linking changes
            $('#my-documents-search-form').on('submit', (e) => {
                e.preventDefault();
                UIRefreshManager.refreshAll();
            });

            $('#btn-reset-document-filters').on('click', () => {
                $('#filter-document-name').val('');
                Utils.clearSelect2('#filter-document-classification');
                UIRefreshManager.refreshAll();
            });
        },
    };

    const ActionsManager = {
        init() {
            // Setup global CSRF for $.post
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).on('click', '.btn-mark-viewed', async function() {
                const docId = $(this).data('id');
                if (!docId) return;

                try {
                    await ApiManager.markViewed(docId);
                    // Refresh silently or after brief delay
                    setTimeout(() => UIRefreshManager.refreshAll(), 1000);
                } catch (error) {
                    console.error('Failed to mark document as viewed', error);
                }
            });

            $(document).on('click', '.btn-acknowledge', async function() {
                const docId = $(this).data('id');
                if (!docId) return;

                // Disable button and show loading state
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="bx bx-loader-circle bx-spin me-1"></i> Acknowledging...');

                try {
                    const response = await ApiManager.acknowledge(docId);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.acknowledge);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    $btn.prop('disabled', false).html('<i class="bx bx-check-circle me-1"></i> Acknowledge');
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.acknowledge);
                }
            });
        }
    };

    const MyDocumentsApp = {
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

    MyDocumentsApp.init();
}
