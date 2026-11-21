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
        // Menu - one click per screen. "Add / Create" lives on each list page.
        // link:  [label, route name, permission(s) "a|b", icon, active route pattern]
        // group: ['label', 'icon', 'id', 'links' => [[label, route, permission, active], ...]]
        //        (a group with only one visible link is shown as a plain link)
        // =========================
        $menuSections = [
            'System Setup' => [
                ['group' => true, 'id' => 'sidebarUsers', 'label' => 'Users', 'icon' => 'solar:user-circle-outline', 'links' => [
                    ['Users', 'admin.users.index', 'users.view', 'admin.users.*'],
                    ['Roles & Permissions', 'admin.roles.index', 'roles.manage', 'admin.roles.*'],
                ]],
                ['Customers', 'customers.index', 'customers.view', 'solar:user-id-outline', 'customers.*'],
                ['Staff', 'staff.index', 'staff.view', 'solar:users-group-rounded-outline', 'staff.*'],
                ['Dress Types', 'dress-types.index', 'dress_types.view', 'solar:t-shirt-outline', 'dress-types.*'],
                ['Measure Templates', 'measurement-templates.index', 'measurement_templates.view', 'solar:ruler-angular-outline', 'measurement-templates.*'],
                ['Workflow Stages', 'workflow-stages.index', 'workflow_stages.view', 'solar:checklist-minimalistic-outline', 'workflow-stages.*'],
            ],
            'Tailoring' => [
                ['Dashboard', 'production.dashboard', 'dashboard.view', 'solar:widget-5-outline', 'production.dashboard|tailoring.production.dashboard'],
                ['Jobs', 'tailoring.jobs.index', 'jobs.view', 'solar:clipboard-list-outline', 'tailoring.jobs.*|tailoring.measurements.*|tailoring.handover.*'],
                ['Work Queue', 'tailoring.workqueue.index', 'workqueue.view|workqueue.all_stages', 'solar:layers-minimalistic-outline', 'tailoring.workqueue.*'],
                ['Pending Bills', 'tailoring.payments.pending', 'payments.view', 'solar:bill-list-outline', 'tailoring.payments.*'],
                ['Staff Report', 'tailoring.reports.staff', 'tailoring_reports.view', 'solar:chart-2-outline', 'tailoring.reports.*'],
            ],
            'Hiring' => [
                ['Hire Items', 'hiring.items.index', 'hire_items.view', 'solar:bag-3-outline', 'hiring.items.*'],
                ['Agreements', 'hiring.agreements.index', 'hire_agreements.view', 'solar:document-text-outline', 'hiring.agreements.*'],
                ['Sales Report', 'hiring.reports.sales', 'hiring_reports.view', 'solar:graph-up-outline', 'hiring.reports.*'],
            ],
        ];

        $menuUser = auth()->user();
        $canAny = fn($perm) => collect(explode('|', $perm))->contains(fn($p) => $menuUser?->can($p));
        $isActive = fn($pattern) => request()->routeIs(...explode('|', $pattern));

        // keep only what this user may open; single-link groups become plain links
        $visibleSections = [];
        foreach ($menuSections as $sectionTitle => $entries) {
            foreach ($entries as $entry) {
                if (!empty($entry['group'])) {
                    $links = array_values(array_filter($entry['links'], fn($l) => $canAny($l[2])));
                    if (count($links) > 1) {
                        $entry['links'] = $links;
                        $visibleSections[$sectionTitle][] = $entry;
                    } elseif (count($links) === 1) {
                        [$label, $routeName, $perm, $active] = $links[0];
                        $visibleSections[$sectionTitle][] = [$label, $routeName, $perm, $entry['icon'], $active];
                    }
                } elseif ($canAny($entry[2])) {
                    $visibleSections[$sectionTitle][] = $entry;
                }
            }
        }
    @endphp

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            @foreach ($visibleSections as $sectionTitle => $entries)
                @if (!$loop->first)
                    <div class="menu-divider"></div>
                @endif

                <li class="menu-title section-title">{{ $sectionTitle }}</li>

                @foreach ($entries as $entry)
                    @if (!empty($entry['group']))
                        @php $groupActive = collect($entry['links'])->contains(fn($l) => $isActive($l[3])); @endphp
                        <li class="nav-item {{ $groupActive ? 'section-active' : '' }}">
                            <a class="nav-link menu-arrow" href="#{{ $entry['id'] }}" data-bs-toggle="collapse" role="button"
                                aria-expanded="{{ $groupActive ? 'true' : 'false' }}" aria-controls="{{ $entry['id'] }}">
                                <span class="nav-icon">
                                    <iconify-icon icon="{{ $entry['icon'] }}"></iconify-icon>
                                </span>
                                <span class="nav-text"> {{ $entry['label'] }}</span>
                            </a>

                            <div class="collapse {{ $groupActive ? 'show' : '' }}" id="{{ $entry['id'] }}">
                                <ul class="nav sub-navbar-nav">
                                    @foreach ($entry['links'] as $link)
                                        <li class="sub-nav-item">
                                            <a class="sub-nav-link {{ $isActive($link[3]) ? 'active' : '' }}"
                                                href="{{ route($link[1]) }}">
                                                {{ $link[0] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ $isActive($entry[4]) ? 'active' : '' }}" href="{{ route($entry[1]) }}">
                                <span class="nav-icon">
                                    <iconify-icon icon="{{ $entry[3] }}"></iconify-icon>
                                </span>
                                <span class="nav-text"> {{ $entry[0] }}</span>
                            </a>
                        </li>
                    @endif
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
