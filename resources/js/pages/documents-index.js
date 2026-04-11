/**
 * documents/index.js
 * Structured page controller for Documents listing.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initDocumentsPage() {
    const pageNode = document.getElementById('documents-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        documents: Utils.createEndpoints(pageNode.dataset, {
            subCompaniesAll: 'subCompaniesAllUrl',
            squadsAll: 'squadsAllUrl',
            show: 'showUrlTemplate',
            statusInfo: 'statusInfoUrlTemplate',
        }),
    };

    const TRANSLATION = {
        error: {
            missingDocument: 'Unable to identify document.',
            missingDocumentForEdit: 'Unable to identify document for editing.',
            loadStats: 'Unable to load document statistics.',
            loadSubCompanies: 'Unable to load sub-companies.',
            loadSquads: 'Unable to load squads.',
            loadDocumentEdit: 'Unable to load document for editing.',
            saveDocument: 'Unable to save document.',
            deleteDocument: 'Unable to delete document.',
        },
        success: {
            saveCreate: 'Document created successfully.',
            saveUpdate: 'Document updated successfully.',
            delete: 'Document deleted successfully.',
        },
        confirm: {
            delete: {
                titlePrefix: 'Delete ',
                confirmText: 'Yes, delete',
            },
        },
        modal: {
            createTitle: 'Create Document',
            editTitle: 'Edit Document',
        },
        options: {
            subCompany: 'Select Sub-Company',
            squad: 'Select Squad',
        },
        loading: {
            text: 'Loading...',
        },
    };

    const DOM = {
        formModal: document.getElementById('documentFormModal'),
        form: document.getElementById('document-form'),
    };

    if (!DOM.formModal || !DOM.form) {
        return;
    }

    const STATE = {
        statsSelectors: [
            '#summary-total',
            '#summary-public',
            '#summary-internal',
            '#summary-confidential',
        ],
    };

    const ScrollManager = Utils.createModalManager();

    const TableManager = {
        table: null,

        init() {
            this.table = Utils.createDataTable(
                '#documents-table',
                ROUTES.documents.datatable,
                [
                    { data: 'name', name: 'name' },
                    { data: 'classification_label', name: 'classification' },
                    { data: 'scope_label', name: 'scope_type' },
                    { 
                        data: 'requires_acknowledgment', 
                        name: 'requires_acknowledgment',
                        render: function(data) {
                            return data ? '<span class="badge bg-warning">Yes</span>' : '<span class="badge bg-secondary">No</span>';
                        }
                    },
                    { data: 'created_at', name: 'created_at' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end text-nowrap',
                    },
                ],
                {
                    ajax: {
                        url: ROUTES.documents.datatable,
                        data: (request) => {
                            request.search_name = String($('#filter-document-name').val() ?? '').trim();
                            request.search_classification = String($('#filter-document-classification').val() ?? '').trim();
                            request.search_scope = String($('#filter-document-scope').val() ?? '').trim();
                        },
                    },
                },
            );
        },

        reload() {
            return Utils.reloadDataTable(this.table);
        },
    };

    const ApiManager = {
        async fetchDocument(docId) {
            const response = await $.get(ROUTES.documents.show(docId));
            return response?.data ?? null; 
        },

        async fetchStatusInfo(docId) {
            const response = await $.get(ROUTES.documents.statusInfo(docId));
            return response?.data ?? [];
        },

        async fetchStats() {
            const response = await $.get(ROUTES.documents.stats);
            return response?.data ?? {};
        },

        async fetchSubCompanies() {
            const response = await $.get(ROUTES.documents.subCompaniesAll);
            return response?.data ?? [];
        },

        async fetchSquads() {
            const response = await $.get(ROUTES.documents.squadsAll);
            return response?.data ?? [];
        },

        async createDocument(formData) {
            return $.ajax({
                url: ROUTES.documents.store,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            });
        },

        async updateDocument(docId, formData) {
            formData.append('_method', 'PUT');
            return $.ajax({
                url: ROUTES.documents.update(docId),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
            });
        },

        async deleteDocument(docId) {
            return $.ajax({
                url: ROUTES.documents.destroy(docId),
                method: 'POST',
                data: { _method: 'DELETE' },
            });
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
                '#summary-public': stats.public ?? 0,
                '#summary-internal': stats.internal ?? 0,
                '#summary-confidential': stats.confidential ?? 0,
            });

            Utils.setLastUpdated(stats.last_update ?? null);
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

    const OptionsManager = {
        async loadScopeTargets(scopeType) {
            const $scopeIdSelect = $('#form-document-scope-id');
            $scopeIdSelect.empty().append('<option value=""></option>');
            
            if (scopeType === 'company') {
                return;
            }

            try {
                let data = [];
                let defaultText = '';
                
                if (scopeType === 'sub_company') {
                    data = await ApiManager.fetchSubCompanies();
                    defaultText = TRANSLATION.options.subCompany;
                } else if (scopeType === 'squad') {
                    data = await ApiManager.fetchSquads();
                    defaultText = TRANSLATION.options.squad;
                }

                Utils.buildSelect2Options(
                    '#form-document-scope-id',
                    data.map((item) => ({
                        value: item.id,
                        label: item.name,
                    })),
                    defaultText,
                );
            } catch (error) {
                Utils.showAlert('danger', TRANSLATION.error.loadSubCompanies);
            }
        }
    };

    const FormManager = {
        reset() {
            DOM.form.reset();

            $('#document_id').val('');
            $('#form-document-classification').val('internal_use_only').trigger('change.select2');
            $('#form-document-scope-type').val('company').trigger('change.select2');
            $('#form-document-upload-type').val('file').trigger('change.select2');
            
            Utils.clearSelect2('#form-document-scope-id');
            $('#scope-id-container').addClass('d-none');
            
            $('#file-upload-container').removeClass('d-none');
            $('#file-url-container').addClass('d-none');
            $('.edit-only').addClass('d-none');
            
            Utils.clearFormErrors('#document-form-errors');
        },

        getSubmitPayload() {
            const id = $('#document_id').val();
            const isEdit = Boolean(id);
            const formData = new FormData(DOM.form);

            return {
                id,
                isEdit,
                formData,
            };
        },
    };

    const UIRefreshManager = {
        async refreshAll() {
            await Utils.refreshTableAndStats({
                table: TableManager.table,
                refreshStats: () => StatsManager.refresh(),
            });
        },
    };

    const FilterManager = {
        init() {
            Utils.initRealtimeFilters({
                formSelector: '#documents-search-form',
                onReload: () => {
                    void TableManager.reload();
                },
                resetButtonSelector: '#btn-reset-document-filters',
                onReset: () => {
                    Utils.clearSelect2('#filter-document-classification');
                    Utils.clearSelect2('#filter-document-scope');
                },
            });
        },
    };

    const ModalFlowManager = {
        async prepareCreate() {
            FormManager.reset();
            $('#document-modal-title').text(TRANSLATION.modal.createTitle);
            ScrollManager.update(DOM.formModal);
        },

        async prepareEdit(docData) {
            FormManager.reset();
            $('#document-modal-title').text(TRANSLATION.modal.editTitle);
            
            $('#document_id').val(docData.id);
            $('#form-document-name').val(docData.name);
            $('#form-document-classification').val(docData.classification).trigger('change.select2');
            $('#form-document-scope-type').val(docData.scope_type).trigger('change.select2');
            
            if (docData.scope_type !== 'company') {
                $('#scope-id-container').removeClass('d-none');
                await OptionsManager.loadScopeTargets(docData.scope_type);
                $('#form-document-scope-id').val(docData.scope_id).trigger('change.select2');
            }
            
            $('.edit-only').removeClass('d-none');
            $('#form-document-upload-type').val('keep').trigger('change.select2');
            $('#file-upload-container').addClass('d-none');
            $('#file-url-container').addClass('d-none');
            
            $('#form-document-requires-ack').prop('checked', docData.requires_acknowledgment);

            ScrollManager.update(DOM.formModal);
        },

        initLifecycleEvents() {
            DOM.formModal.addEventListener('shown.bs.modal', () => {
                ScrollManager.init(DOM.formModal);
                ScrollManager.update(DOM.formModal);
            });

            DOM.formModal.addEventListener('show.bs.modal', async (event) => {
                const trigger = event.relatedTarget;

                if (!(trigger instanceof HTMLElement)) {
                    await this.prepareCreate();
                    return;
                }

                const isEdit = trigger.dataset.modalMode === 'edit' || trigger.classList.contains('editDocumentBtn');

                if (!isEdit) {
                    await this.prepareCreate();
                    return;
                }

                const docId = trigger.dataset.id;

                if (!docId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingDocumentForEdit);
                    event.preventDefault();
                    return;
                }

                try {
                    const rowData = await ApiManager.fetchDocument(docId);
                    if (rowData) {
                        await this.prepareEdit(rowData);
                    } else {
                        throw new Error('Not found');
                    }
                } catch(error) {
                     Utils.showAlert('danger', TRANSLATION.error.loadDocumentEdit);
                     event.preventDefault();
                }
            });

            const statusModal = document.getElementById('documentStatusModal');
            if (statusModal) {
                statusModal.addEventListener('show.bs.modal', async (event) => {
                    const trigger = event.relatedTarget;
                    const docId = trigger.dataset.id;
                    const docName = trigger.dataset.name;

                    if (!docId) {
                        event.preventDefault();
                        return;
                    }

                    $('#document-status-modal-title').text(`Status: ${docName}`);
                    
                    const $tbody = $('#document-status-tbody');
                    $tbody.html('<tr><td colspan="3" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>');

                    try {
                        const employees = await ApiManager.fetchStatusInfo(docId);
                        
                        $tbody.empty();
                        
                        if (employees.length === 0) {
                            $tbody.html('<tr><td colspan="3" class="text-center py-4 text-muted">No employees are required to view this document.</td></tr>');
                            return;
                        }

                        employees.forEach(emp => {
                            let statusBadge = '';
                            if (emp.status_raw === 'acknowledged') {
                                statusBadge = `<span class="badge bg-success">${emp.status}</span>`;
                            } else if (emp.status_raw === 'viewed') {
                                statusBadge = `<span class="badge bg-info">${emp.status}</span>`;
                            } else {
                                statusBadge = `<span class="badge bg-secondary">${emp.status}</span>`;
                            }

                            const ackAt = emp.acknowledged_at ? emp.acknowledged_at : '<span class="text-muted">-</span>';

                            $tbody.append(`
                                <tr>
                                    <td class="fw-medium">${emp.full_name}</td>
                                    <td>${statusBadge}</td>
                                    <td>${ackAt}</td>
                                </tr>
                            `);
                        });
                    } catch (error) {
                        $tbody.html('<tr><td colspan="3" class="text-center py-4 text-danger">Failed to load status information.</td></tr>');
                    }
                });
            }

            DOM.formModal.addEventListener('hidden.bs.modal', () => {
                Utils.setFormLoading('#document-form', false);
                Utils.clearFormErrors('#document-form-errors');
                ScrollManager.destroy(DOM.formModal);
            });
            
            // Scope type change event
            $('#form-document-scope-type').on('change', function() {
                const type = $(this).val();
                if (type === 'company') {
                    $('#scope-id-container').addClass('d-none');
                    $('#form-document-scope-id').val('').trigger('change.select2');
                } else {
                    $('#scope-id-container').removeClass('d-none');
                    OptionsManager.loadScopeTargets(type);
                }
            });
            
            // Upload type change event
            $('#form-document-upload-type').on('change', function() {
                const type = $(this).val();
                $('#file-upload-container').toggleClass('d-none', type !== 'file');
                $('#file-url-container').toggleClass('d-none', type !== 'url');
            });
        },
    };

    const DeleteManager = {
        init() {
            $(document).on('click', '.deleteDocumentBtn', async function handleDeleteDocument() {
                const docId = $(this).data('id');
                const docName = $(this).data('name');

                if (!docId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingDocument);

                    return;
                }

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.delete.titlePrefix}${docName}?`,
                    confirmText: TRANSLATION.confirm.delete.confirmText,
                });

                if (!confirmed) {
                    return;
                }

                try {
                    const response = await ApiManager.deleteDocument(docId);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.delete);
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.deleteDocument);
                }
            });
        },
    };

    const FormSubmissionManager = {
        init() {
            $('#document-form').on('submit', async function submitDocumentForm(event) {
                event.preventDefault();

                const $submitBtn = $(this).find('button[type="submit"]');
                
                // Form validation markup
                if (!this.checkValidity()) {
                    event.stopPropagation();
                    this.classList.add('was-validated');
                    return;
                }
                
                Utils.clearFormErrors('#document-form-errors');

                const { id, isEdit, formData } = FormManager.getSubmitPayload();
                
                Utils.setBtnLoading($submitBtn, true);

                try {
                    const response = isEdit
                        ? await ApiManager.updateDocument(id, formData)
                        : await ApiManager.createDocument(formData);

                    ScrollManager.hideModal(DOM.formModal);
                    Utils.showAlert('success', response.message ?? (isEdit ? TRANSLATION.success.saveUpdate : TRANSLATION.success.saveCreate));
                    await UIRefreshManager.refreshAll();
                } catch (error) {
                    if (error.status === 422 && error.responseJSON?.errors) {
                        Utils.showFormErrors('#document-form-errors', error.responseJSON.errors);

                        return;
                    }

                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.saveDocument);
                } finally {
                    Utils.setBtnLoading($submitBtn, false);
                }
            });
        },
    };

    const DocumentApp = {
        initPlugins() {
            Utils.initSelect2('.select2-filter');
            Utils.initSelect2('.select2-init', $('#documentFormModal'));
        },

        initEvents() {
            FilterManager.init();
            ModalFlowManager.initLifecycleEvents();
            DeleteManager.init();
            FormSubmissionManager.init();
        },

        initData() {
            void StatsManager.refresh();
        },

        init() {
            this.initPlugins();
            TableManager.init();
            this.initEvents();
            this.initData();
        },
    };

    DocumentApp.init();
}
