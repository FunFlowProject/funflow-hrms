/**
 * work-logs/index.js
 * Controller for Work Logs page and dynamic entry form.
 */

'use strict';

import * as helpers from '../helpers.js';

export function initWorkLogsPage() {
    const pageNode = document.getElementById('work-logs-page');

    if (!pageNode || !window.$ || !window.bootstrap || !window.Swal) {
        return;
    }

    const $ = window.$;
    const Utils = helpers;

    const ROUTES = {
        workLogs: Utils.createEndpoints(pageNode.dataset),
    };

    const TRANSLATION = {
        error: {
            missingLog: 'Unable to identify work log.',
            missingLogForEdit: 'Unable to identify work log for editing.',
            loadLogEdit: 'Unable to load work log for editing.',
            saveLog: 'Unable to save work log.',
            deleteLog: 'Unable to delete work log.',
        },
        success: {
            saveCreate: 'Work log saved successfully.',
            saveUpdate: 'Work log updated successfully.',
            delete: 'Work log deleted successfully.',
        },
        confirm: {
            delete: {
                titlePrefix: 'Delete work log from ',
                confirmText: 'Yes, delete',
            },
        },
        modal: {
            createTitle: 'Log Work',
            editTitle: 'Edit Work Log',
        },
    };

    const DOM = {
        formModal: document.getElementById('workLogFormModal'),
        viewModal: document.getElementById('workLogViewModal'),
        form: document.getElementById('work-log-form'),
        tasksContainer: document.getElementById('tasks-container'),
        addTaskBtn: document.getElementById('btn-add-task-row'),
        rowTemplate: document.getElementById('task-row-template'),
        displayTotalDuration: document.getElementById('display-total-duration'),
        emptyPlaceholder: document.getElementById('empty-tasks-placeholder'),
    };

    if (!DOM.formModal || !DOM.form || !DOM.tasksContainer || !DOM.rowTemplate) {
        return;
    }

    const STATE = {
        isMyLogs: pageNode.dataset.isMyLogs === 'true',
    };

    const ScrollManager = Utils.createModalManager();

    const TableManager = {
        table: null,

        init() {
            const columns = [
                { data: 'tasks_count', name: 'tasks_count', className: 'text-center' },
                { data: 'total_duration', name: 'total_duration', className: 'fw-bold text-primary' },
                { data: 'created_at', name: 'created_at' },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'text-end text-nowrap',
                },
            ];

            if (!STATE.isMyLogs) {
                columns.unshift({ data: 'employee', name: 'employee' });
            }

            this.table = Utils.createDataTable(
                '#work-logs-table',
                ROUTES.workLogs.datatable,
                columns
            );
        },

        reload() {
            return Utils.reloadDataTable(this.table);
        },
    };

    const ApiManager = {
        async fetchLog(logId) {
            const response = await $.get(ROUTES.workLogs.show(logId));
            return response?.data;
        },

        async createLog(payload) {
            return $.ajax({
                url: ROUTES.workLogs.store,
                method: 'POST',
                data: $.param(payload),
            });
        },

        async updateLog(logId, payload) {
            return $.ajax({
                url: ROUTES.workLogs.update(logId),
                method: 'POST',
                data: $.param([...payload, { name: '_method', value: 'PUT' }]),
            });
        },

        async deleteLog(logId) {
            return $.ajax({
                url: ROUTES.workLogs.destroy(logId),
                method: 'POST',
                data: { _method: 'DELETE' },
            });
        },
    };

    const TaskRowManager = {
        index: 0,

        init() {
            DOM.addTaskBtn.addEventListener('click', () => this.addRow());
            
            $(DOM.tasksContainer).on('click', '.btn-remove-task-row', (e) => {
                const row = e.target.closest('.task-row');
                row?.remove();
                this.updateTotalDuration();
                this.checkEmptyState();
            });

            $(DOM.tasksContainer).on('input', '.task-duration-input', () => {
                this.updateTotalDuration();
            });
        },

        addRow(data = null) {
            const html = DOM.rowTemplate.innerHTML.replace(/__INDEX__/g, this.index);
            const $row = $(html);
            
            if (data) {
                $row.find('.task-name-input').val(data.name || '');
                $row.find('.task-duration-input').val(data.duration_minutes || '');
                $row.find('.task-done-checkbox').prop('checked', data.done !== false);
            }

            $(DOM.tasksContainer).append($row);
            this.index++;
            this.checkEmptyState();
        },

        clear() {
            DOM.tasksContainer.innerHTML = '';
            this.index = 0;
            this.updateTotalDuration();
            this.checkEmptyState();
        },

        checkEmptyState() {
            const rowCount = $(DOM.tasksContainer).find('.task-row').length;
            if (rowCount === 0) {
                DOM.emptyPlaceholder.classList.remove('d-none');
            } else {
                DOM.emptyPlaceholder.classList.add('d-none');
            }
        },

        updateTotalDuration() {
            let total = 0;
            $(DOM.tasksContainer).find('.task-duration-input').each(function() {
                total += parseInt($(this).val() || 0);
            });

            const hours = Math.floor(total / 60);
            const minutes = total % 60;
            
            DOM.displayTotalDuration.textContent = `${hours}h ${minutes}m`;
        }
    };

    const FormManager = {
        reset() {
            DOM.form.reset();
            $('#work_log_id').val('');
            TaskRowManager.clear();
            Utils.clearFormErrors('#work-log-form-errors');
            // By default add one empty row
            TaskRowManager.addRow();
        },

        populate(workLog) {
            $('#work_log_id').val(workLog.id ?? '');
            
            TaskRowManager.clear();
            if (Array.isArray(workLog.tasks)) {
                workLog.tasks.forEach(task => TaskRowManager.addRow(task));
            } else {
                TaskRowManager.addRow();
            }
            
            TaskRowManager.updateTotalDuration();
        },

        getSubmitPayload() {
            const id = $('#work_log_id').val();
            const isEdit = Boolean(id);
            const payload = $('#work-log-form').serializeArray();

            return { id, isEdit, payload };
        },
    };

    const ViewManager = {
        setLoading() {
            [
                '#view-employee-name',
                '#view-total-duration',
                '#view-created-at'
            ].forEach(sel => $(sel).text('Loading...'));
            $('#view-tasks-list').empty();
        },

        populate(workLog) {
            $('#view-employee-name').text(workLog.user_name || '-');
            $('#view-total-duration').text(workLog.total_duration_formatted || '-');
            $('#view-created-at').text(workLog.created_at || '-');

            const $list = $('#view-tasks-list').empty();
            if (Array.isArray(workLog.tasks) && workLog.tasks.length > 0) {
                workLog.tasks.forEach(task => {
                    const statusIcon = task.done 
                        ? '<i class="bx bx-check-circle text-success me-2"></i>' 
                        : '<i class="bx bx-circle text-warning me-2"></i>';
                    
                    const item = `
                        <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <div class="d-flex align-items-center">
                                ${statusIcon}
                                <span class="fw-semibold text-dark">${task.name}</span>
                            </div>
                            <span class="badge bg-label-primary rounded-pill">${task.duration_minutes} min</span>
                        </div>
                    `;
                    $list.append(item);
                });
            } else {
                $list.append('<div class="p-3 text-muted text-center">No tasks recorded.</div>');
            }
        }
    };

    const ModalFlowManager = {
        initLifecycleEvents() {
            DOM.formModal.addEventListener('shown.bs.modal', () => {
                ScrollManager.init(DOM.formModal);
                ScrollManager.update(DOM.formModal);
            });

            DOM.formModal.addEventListener('show.bs.modal', async (event) => {
                const trigger = event.relatedTarget;
                const isEdit = trigger?.dataset.modalMode === 'edit' || trigger?.classList.contains('editWorkLogBtn');

                if (!isEdit) {
                    FormManager.reset();
                    return;
                }

                const logId = trigger.dataset.id;
                if (!logId) {
                    Utils.showAlert('danger', TRANSLATION.error.missingLogForEdit);
                    event.preventDefault();
                    return;
                }

                FormManager.reset();
                Utils.setFormLoading('#work-log-form', true);
                
                try {
                    const workLog = await ApiManager.fetchLog(logId);
                    FormManager.populate(workLog);
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.loadLogEdit);
                    ScrollManager.hideModal(DOM.formModal);
                } finally {
                    Utils.setFormLoading('#work-log-form', false);
                    ScrollManager.update(DOM.formModal);
                }
            });

            DOM.formModal.addEventListener('hidden.bs.modal', () => {
                ScrollManager.destroy(DOM.formModal);
            });

            if (DOM.viewModal) {
                DOM.viewModal.addEventListener('show.bs.modal', async (event) => {
                    const trigger = event.relatedTarget;
                    const logId = trigger?.dataset.id;
                    if (!logId) return event.preventDefault();

                    ViewManager.setLoading();
                    
                    try {
                        const workLog = await ApiManager.fetchLog(logId);
                        ViewManager.populate(workLog);
                        ScrollManager.update(DOM.viewModal);
                    } catch (error) {
                        Utils.showAlert('danger', error.responseJSON?.message ?? 'Unable to load details.');
                        ScrollManager.hideModal(DOM.viewModal);
                    }
                });

                DOM.viewModal.addEventListener('hidden.bs.modal', () => {
                    ScrollManager.destroy(DOM.viewModal);
                });
            }
        },
    };

    const FormSubmissionManager = {
        init() {
            $('#work-log-form').on('submit', async function(event) {
                event.preventDefault();

                const $submitBtn = $(this).find('button[type="submit"]');
                Utils.clearFormErrors('#work-log-form-errors');

                const { id, isEdit, payload } = FormManager.getSubmitPayload();
                Utils.setBtnLoading($submitBtn, true);

                try {
                    const response = isEdit
                        ? await ApiManager.updateLog(id, payload)
                        : await ApiManager.createLog(payload);

                    ScrollManager.hideModal(DOM.formModal);
                    Utils.showAlert('success', response.message ?? (isEdit ? TRANSLATION.success.saveUpdate : TRANSLATION.success.saveCreate));
                    void TableManager.reload();
                } catch (error) {
                    if (error.status === 422 && error.responseJSON?.errors) {
                        Utils.showFormErrors('#work-log-form-errors', error.responseJSON.errors);
                        return;
                    }
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.saveLog);
                } finally {
                    Utils.setBtnLoading($submitBtn, false);
                }
            });
        },
    };

    const DeleteManager = {
        init() {
            $(document).on('click', '.deleteWorkLogBtn', async function() {
                const logId = $(this).data('id');
                const date = $(this).data('date') || 'this log';

                if (!logId) return;

                const confirmed = await Utils.confirmAction({
                    title: `${TRANSLATION.confirm.delete.titlePrefix}${date}?`,
                    confirmText: TRANSLATION.confirm.delete.confirmText,
                });

                if (!confirmed) return;

                try {
                    const response = await ApiManager.deleteLog(logId);
                    Utils.showAlert('success', response.message ?? TRANSLATION.success.delete);
                    void TableManager.reload();
                } catch (error) {
                    Utils.showAlert('danger', error.responseJSON?.message ?? TRANSLATION.error.deleteLog);
                }
            });
        },
    };

    const FilterManager = {
        init() {
            Utils.initRealtimeFilters({
                formSelector: '#work-logs-search-form',
                onReload: () => void TableManager.reload(),
                resetButtonSelector: '#btn-reset-work-log-filters',
            });
        },
    };

    const WorkLogApp = {
        init() {
            TableManager.init();
            TaskRowManager.init();
            ModalFlowManager.initLifecycleEvents();
            FormSubmissionManager.init();
            DeleteManager.init();
            FilterManager.init();
        },
    };

    // Helper for duration display
    function floor(n) { return Math.floor(n); }

    WorkLogApp.init();
}
