<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <x-ui.logo :src="asset('assets/img/funflow-logo.png')" size="34" img-class="funflow-logo-sidebar"
                alt="Funflow" />
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div>Dashboard</div>
            </a>
        </li>

        @can('work-logs.my.view')
            <li class="menu-item {{ request()->routeIs('my-work-logs.*') ? 'active' : '' }}">
                <a href="{{ route('my-work-logs.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-calendar-check"></i>
                    <div>My Work Logs</div>
                </a>
            </li>
        @endcan

        @can('work-logs.view-all')
            <li class="menu-item {{ request()->routeIs('work-logs.*') ? 'active' : '' }}">
                <a href="{{ route('work-logs.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-time-five"></i>
                    <div>Work Logs</div>
                </a>
            </li>
        @endcan

        <li class="menu-item">
            <a href="https://funflow.org/emp-email-login" target="_blank" class="menu-link">
                <i class="menu-icon tf-icons bx bx-envelope"></i>
                <div>Mailbox</div>
            </a>
        </li>

        @can('employees.view')
            <li class="menu-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <a href="{{ route('employees.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div>Employees</div>
                </a>
            </li>
        @endcan

        @canany(['sub-companies.view', 'squads.view'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Organization</span>
            </li>

            @can('sub-companies.view')
                <li class="menu-item {{ request()->routeIs('sub-companies.*') ? 'active' : '' }}">
                    <a href="{{ route('sub-companies.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-buildings"></i>
                        <div>Sub-Companies</div>
                    </a>
                </li>
            @endcan

            @can('squads.view')
                <li class="menu-item {{ request()->routeIs('squads.*') ? 'active' : '' }}">
                    <a href="{{ route('squads.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-grid-alt"></i>
                        <div>Squads</div>
                    </a>
                </li>
            @endcan
        @endcanany

        @canany(['service-catalog.view', 'service-requests.manage'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Service Desk</span>
            </li>
        @endcanany

        @can('service-catalog.view')
            <li class="menu-item {{ request()->routeIs('service-catalog.*') ? 'active' : '' }}">
                <a href="{{ route('service-catalog.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-briefcase-alt-2"></i>
                    <div>Service Catalog</div>
                </a>
            </li>
        @endcan

        @can('service-requests.manage')
            <li class="menu-item {{ request()->routeIs('service-requests.*') ? 'active' : '' }}">
                <a href="{{ route('service-requests.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-message-dots"></i>
                    <div>Service Requests</div>
                </a>
            </li>
        @elsecan('service-requests.view')
            <li class="menu-item {{ request()->routeIs('my-service-requests.*') ? 'active' : '' }}">
                <a href="{{ route('my-service-requests.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-message-dots"></i>
                    <div>My Service Requests</div>
                </a>
            </li>
        @endcan

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Documents</span>
        </li>
        @can('documents.view')
            <li class="menu-item {{ request()->routeIs('documents.*') ? 'active' : '' }}">
                <a href="{{ route('documents.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-file"></i>
                    <div>Document Management</div>
                </a>
            </li>
        @endcan

        @can('documents.my.view')
            <li class="menu-item {{ request()->routeIs('my-documents.*') ? 'active' : '' }}">
                <a href="{{ route('my-documents.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-collection"></i>
                    <div>My Documents</div>
                </a>
            </li>
        @endcan

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Development</span>
        </li>
        @canany(['educational-objectives.manage', 'educational-objectives.manage-all'])
            <li class="menu-item {{ request()->routeIs('educational-objectives.*') ? 'active' : '' }}">
                <a href="{{ route('educational-objectives.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book-open"></i>
                    <div>Management</div>
                </a>
            </li>
        @endcanany

        @can('educational-objectives.my.view')
            <li class="menu-item {{ request()->routeIs('my-objectives.*') ? 'active' : '' }}">
                <a href="{{ route('my-objectives.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
                    <div>My Objectives</div>
                </a>
            </li>
        @endcan
    </ul>
</aside>