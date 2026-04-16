import './bootstrap';
import * as bootstrap from 'bootstrap';
import $ from 'jquery';
import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';
import select2 from 'select2';
import flatpickr from 'flatpickr';
import Swal from 'sweetalert2';

import * as helpers from './helpers.js';
import { initLayout } from './layout';
import { initAdminDashboardPage } from './pages/admin-dashboard';
import { initEmployeeDashboardPage } from './pages/employee-dashboard';
import { initHrDashboardPage } from './pages/hr-dashboard';
import { initEmployeesPage } from './pages/employees-index';
import { initServiceCatalogPage } from './pages/service-catalog-index';
import { initServiceRequestsPage } from './pages/service-requests-index';
import { initProfitPage } from './pages/profit-index';
import { initMyProfitPage } from './pages/profit-my';
import { initSubCompaniesPage } from './pages/sub-companies-index';
import { initSquadsPage } from './pages/squads-index';
import { initDocumentsPage } from './pages/documents-index';
import { initMyDocumentsPage } from './pages/documents-my-documents';
import { initEducationalObjectivesPage } from './pages/educational-objectives-index';
import { initMyObjectivesPage } from './pages/educational-objectives-my';
import { initWorkLogsPage } from './pages/work-logs-index';

window.bootstrap = bootstrap;
window.$ = $;
window.jQuery = $;
window.flatpickr = flatpickr;
window.Swal = Swal;
window.helpers = helpers;

DataTable.use('bootstrap', bootstrap);
select2(window, $);

document.addEventListener('DOMContentLoaded', () => {
    window.helpers.setupAjax();
    initLayout();
    initAdminDashboardPage();
    initHrDashboardPage();
    initEmployeeDashboardPage();
    initEmployeesPage();
    initSubCompaniesPage();
    initSquadsPage();
    initServiceCatalogPage();
    initServiceRequestsPage();
    initProfitPage();
    initMyProfitPage();
    initDocumentsPage();
    initMyDocumentsPage();
    initEducationalObjectivesPage();
    initMyObjectivesPage();
    initWorkLogsPage();
});
