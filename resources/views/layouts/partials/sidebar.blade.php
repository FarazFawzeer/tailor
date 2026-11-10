<div class="app-sidebar">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="{{ route('production.dashboard') }}" class="logo-dark">
            <img src="/images/logo.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo.png" class="logo-lg" alt="logo dark" style="width: 150px; height: 120px;">
        </a>

        <a href="{{ route('production.dashboard') }}" class="logo-light">
            <img src="/images/logo.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo.png" class="logo-lg" alt="logo light" style="width: 150px; height: 120px;">
        </a>
    </div>


    <style>
        .app-sidebar {
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .app-sidebar .logo-box {
            flex-shrink: 0;
        }

        .app-sidebar .scrollbar {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Section Titles */
        .menu-title.section-title {
            margin: 14px 12px 8px;
            padding: 8px 12px;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .04em;
            text-transform: uppercase;
            background: rgba(13, 110, 253, .08);
            border: 1px solid rgba(13, 110, 253, .15);
        }

        .menu-divider {
            margin: 10px 12px;
            height: 1px;
            background: rgba(0, 0, 0, .08);
        }

        .nav-link.menu-arrow {
            border-radius: 12px;
            padding: 10px 12px;
            transition: all .15s ease;
        }

        .nav-link.menu-arrow:hover {
            background: rgba(13, 110, 253, .06);
        }

        .nav-item.section-active>.nav-link {
            background: transparent !important;
            border: none !important;
            border-radius: 0 !important;
        }

        .sub-navbar-nav .sub-nav-link {
            border-radius: 10px;
            padding: 8px 12px;
        }

        /* Better mobile sidebar scrolling */
        @media (max-width: 991.98px) {
            .app-sidebar {
                height: 100vh;
                max-height: 100vh;
            }

            .app-sidebar .scrollbar {
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Section Titles */
        .menu-title.section-title {
            margin: 14px 12px 8px;
            padding: 8px 12px;

            font-weight: 700;
            font-size: 12px;
            letter-spacing: .04em;
            text-transform: uppercase;
            background: rgba(13, 110, 253, .08);

            border: 1px solid rgba(13, 110, 253, .15);
        }

        /* Subtle separator for blocks */
        .menu-divider {
            margin: 10px 12px;
            height: 1px;
            background: rgba(0, 0, 0, .08);
        }

        /* Make main menu arrows look clean */
        .nav-link.menu-arrow {
            border-radius: 12px;
            padding: 10px 12px;
            transition: all .15s ease;
        }

        .nav-link.menu-arrow:hover {
            background: rgba(13, 110, 253, .06);
        }

        /* Highlight section (Tailoring/Hiring) when active */
        /* Remove highlight for active section */
        .nav-item.section-active>.nav-link {
            background: transparent !important;
            border: none !important;
            border-radius: 0 !important;
        }

        /* Sub menu links */
        .sub-navbar-nav .sub-nav-link {
            border-radius: 10px;
            padding: 8px 12px;
        }
    </style>

    @php
        // =========================
        // Menu: each link shows only when the user has its permission
        // [label, route name, permission(s) "a|b", active route pattern]
        // =========================
        $menuSections = [
            'System Setup' => [
                ['id' => 'sidebarUsers', 'label' => 'Users', 'icon' => 'solar:user-circle-outline', 'active' => ['admin.users.*', 'admin.roles.*'], 'links' => [
                    // "+ Add User" is on the users list page
                    ['View Users', 'admin.users.index', 'users.view', 'admin.users.*'],
                    ['Roles & Permissions', 'admin.roles.index', 'roles.manage', 'admin.roles.*'],
                ]],
                ['id' => 'sidebarCustomer', 'label' => 'Customer', 'icon' => 'solar:user-circle-outline', 'active' => ['customers.*'], 'links' => [
                    ['Create', 'customers.create', 'customers.create', 'customers.create'],
                    ['View', 'customers.index', 'customers.view', 'customers.index'],
                ]],
                ['id' => 'sidebarStaff', 'label' => 'Staff', 'icon' => 'solar:users-group-rounded-outline', 'active' => ['staff.*'], 'links' => [
                    ['Create', 'staff.create', 'staff.create', 'staff.create'],
                    ['View', 'staff.index', 'staff.view', 'staff.index'],
                ]],
                ['id' => 'sidebarDressTypes', 'label' => 'Dress Types', 'icon' => 'solar:t-shirt-outline', 'active' => ['dress-types.*'], 'links' => [
                    ['Create', 'dress-types.create', 'dress_types.create', 'dress-types.create'],
                    ['View', 'dress-types.index', 'dress_types.view', 'dress-types.index'],
                ]],
                ['id' => 'sidebarMeasurementTemplates', 'label' => 'Measure Templates', 'icon' => 'solar:ruler-angular-outline', 'active' => ['measurement-templates.*'], 'links' => [
                    ['Create', 'measurement-templates.create', 'measurement_templates.create', 'measurement-templates.create'],
                    ['View', 'measurement-templates.index', 'measurement_templates.view', 'measurement-templates.index'],
                ]],
                ['id' => 'sidebarWorkflowStages', 'label' => 'Workflow Stages', 'icon' => 'solar:checklist-minimalistic-outline', 'active' => ['workflow-stages.*'], 'links' => [
                    ['Create', 'workflow-stages.create', 'workflow_stages.create', 'workflow-stages.create'],
                    ['View', 'workflow-stages.index', 'workflow_stages.view', 'workflow-stages.index'],
                ]],
            ],
            'Tailoring Section' => [
                ['id' => 'sidebarTailoring', 'label' => 'Tailoring', 'icon' => 'solar:clipboard-list-outline', 'active' => ['tailoring.*'], 'links' => [
                    ['Create Job', 'tailoring.jobs.createWizard', 'jobs.create', 'tailoring.jobs.createWizard'],
                    ['View Jobs', 'tailoring.jobs.index', 'jobs.view', 'tailoring.jobs.index'],
                    ['Work Queue', 'tailoring.workqueue.index', 'workqueue.view|workqueue.all_stages', 'tailoring.workqueue.*'],
                    ['Staff Report', 'tailoring.reports.staff', 'tailoring_reports.view', 'tailoring.reports.staff'],
                ]],
            ],
            'Hiring Section' => [
                ['id' => 'sidebarHiring', 'label' => 'Hiring', 'icon' => 'solar:bag-3-outline', 'active' => ['hiring.*'], 'links' => [
                    ['Add Hire Item', 'hiring.items.create', 'hire_items.create', 'hiring.items.create'],
                    ['View Hire Items', 'hiring.items.index', 'hire_items.view', 'hiring.items.index'],
                    ['Create Agreement', 'hiring.agreements.create', 'hire_agreements.create', 'hiring.agreements.create'],
                    ['View Agreements', 'hiring.agreements.index', 'hire_agreements.view', 'hiring.agreements.index'],
                    ['Hiring Sales Report', 'hiring.reports.sales', 'hiring_reports.view', 'hiring.reports.sales'],
                ]],
            ],
        ];

        $menuUser = auth()->user();
        $canAny = fn($perm) => collect(explode('|', $perm))->contains(fn($p) => $menuUser?->can($p));

        // keep only the links (and groups / sections) this user is allowed to see
        $visibleSections = [];
        foreach ($menuSections as $sectionTitle => $groups) {
            foreach ($groups as $group) {
                $group['links'] = array_values(array_filter($group['links'], fn($l) => $canAny($l[2])));
                if ($group['links']) {
                    $visibleSections[$sectionTitle][] = $group;
                }
            }
        }
    @endphp

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            @foreach ($visibleSections as $sectionTitle => $groups)
                @if (!$loop->first)
                    <div class="menu-divider"></div>
                @endif

                <li class="menu-title section-title">{{ $sectionTitle }}</li>

                @foreach ($groups as $group)
                    @php $groupActive = request()->routeIs(...$group['active']); @endphp
                    <li class="nav-item {{ $groupActive ? 'section-active' : '' }}">
                        <a class="nav-link menu-arrow" href="#{{ $group['id'] }}" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ $groupActive ? 'true' : 'false' }}" aria-controls="{{ $group['id'] }}">
                            <span class="nav-icon">
                                <iconify-icon icon="{{ $group['icon'] }}"></iconify-icon>
                            </span>
                            <span class="nav-text"> {{ $group['label'] }}</span>
                        </a>

                        <div class="collapse {{ $groupActive ? 'show' : '' }}" id="{{ $group['id'] }}">
                            <ul class="nav sub-navbar-nav">
                                @foreach ($group['links'] as $link)
                                    <li class="sub-nav-item">
                                        <a class="sub-nav-link {{ request()->routeIs($link[3]) ? 'active' : '' }}"
                                            href="{{ route($link[1]) }}">
                                            {{ $link[0] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @endforeach

                {{-- PROFILE (everyone), at the end of the first section --}}
                @if ($loop->first)
                    @include('layouts.partials.sidebar-profile-link')
                @endif
            @endforeach

            {{-- a user with no other menus still gets Profile --}}
            @if (empty($visibleSections))
                @include('layouts.partials.sidebar-profile-link')
            @endif

        </ul>
    </div>
</div>
