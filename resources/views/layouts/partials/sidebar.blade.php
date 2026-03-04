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

    @php
        // =========================
        // Section detection
        // =========================
        $isTailoring = request()->is('tailoring/*') || request()->routeIs('tailoring.*');
        $isHiring    = request()->is('hiring/*') || request()->routeIs('hiring.*');

        $isAdminSection =
            request()->routeIs('admin.users.*') ||
            request()->routeIs('second') ||
            request()->routeIs('customers.*') ||
            request()->routeIs('staff.*') ||
            request()->routeIs('dress-types.*') ||
            request()->routeIs('measurement-templates.*') ||
            request()->routeIs('workflow-stages.*');

        // Expand collapses automatically when inside section
        $expandHiring    = $isHiring ? 'true' : 'false';
        $expandTailoring = $isTailoring ? 'true' : 'false';
    @endphp

    <style>
        /* Section Titles */
        .menu-title.section-title{
            margin: 14px 12px 8px;
            padding: 8px 12px;
   
            font-weight: 700;
            font-size: 12px;
            letter-spacing: .04em;
            text-transform: uppercase;
            background: rgba(13,110,253,.08);
 
            border: 1px solid rgba(13,110,253,.15);
        }

        /* Subtle separator for blocks */
        .menu-divider{
            margin: 10px 12px;
            height: 1px;
            background: rgba(0,0,0,.08);
        }

        /* Make main menu arrows look clean */
        .nav-link.menu-arrow{
            border-radius: 12px;
            padding: 10px 12px;
            transition: all .15s ease;
        }
        .nav-link.menu-arrow:hover{
            background: rgba(13,110,253,.06);
        }

        /* Highlight section (Tailoring/Hiring) when active */
        .nav-item.section-active > .nav-link{
            background: rgba(13,110,253,.10);
            border: 1px solid rgba(13,110,253,.20);
        }

        /* Sub menu links */
        .sub-navbar-nav .sub-nav-link{
            border-radius: 10px;
            padding: 8px 12px;
        }
     
    </style>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            {{-- =========================
                FIXED SECTION
            ========================== --}}

                  <li class="menu-title section-title">System Setup</li>

            {{-- ADMIN (only super_admin/admin) --}}
            @role('super_admin|admin')
                <li class="nav-item {{ $isAdminSection ? 'section-active' : '' }}">
                    <a class="nav-link menu-arrow" href="#sidebarAdmin" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ request()->routeIs('admin.users.*') || request()->routeIs('second') ? 'true' : 'false' }}"
                        aria-controls="sidebarAdmin">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:user-circle-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Admin</span>
                    </a>

                    <div class="collapse {{ request()->routeIs('admin.users.*') || request()->routeIs('second') ? 'show' : '' }}"
                        id="sidebarAdmin">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('second') ? 'active' : '' }}"
                                    href="{{ route('second', ['admin', 'create']) }}">
                                    Create
                                </a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}"
                                    href="{{ route('admin.users.index') }}">
                                    View
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endrole

            {{-- CUSTOMER (super_admin/admin/front_desk) --}}
            @role('super_admin|admin|front_desk')
                <li class="nav-item {{ request()->routeIs('customers.*') ? 'section-active' : '' }}">
                    <a class="nav-link menu-arrow" href="#sidebarCustomer" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ request()->routeIs('customers.*') ? 'true' : 'false' }}"
                        aria-controls="sidebarCustomer">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:user-circle-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Customer</span>
                    </a>

                    <div class="collapse {{ request()->routeIs('customers.*') ? 'show' : '' }}" id="sidebarCustomer">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('customers.create') ? 'active' : '' }}"
                                    href="{{ route('customers.create') }}">
                                    Create
                                </a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('customers.index') ? 'active' : '' }}"
                                    href="{{ route('customers.index') }}">
                                    View
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endrole

            {{-- STAFF (only super_admin/admin) --}}
            @role('super_admin|admin')
                <li class="nav-item {{ request()->routeIs('staff.*') ? 'section-active' : '' }}">
                    <a class="nav-link menu-arrow" href="#sidebarStaff" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ request()->routeIs('staff.*') ? 'true' : 'false' }}"
                        aria-controls="sidebarStaff">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:users-group-rounded-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Staff</span>
                    </a>

                    <div class="collapse {{ request()->routeIs('staff.*') ? 'show' : '' }}" id="sidebarStaff">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('staff.create') ? 'active' : '' }}"
                                    href="{{ route('staff.create') }}">
                                    Create
                                </a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('staff.index') ? 'active' : '' }}"
                                    href="{{ route('staff.index') }}">
                                    View
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endrole

            {{-- DRESS TYPES (only super_admin/admin) --}}
            @role('super_admin|admin')
                <li class="nav-item {{ request()->routeIs('dress-types.*') ? 'section-active' : '' }}">
                    <a class="nav-link menu-arrow" href="#sidebarDressTypes" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ request()->routeIs('dress-types.*') ? 'true' : 'false' }}"
                        aria-controls="sidebarDressTypes">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:t-shirt-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Dress Types</span>
                    </a>

                    <div class="collapse {{ request()->routeIs('dress-types.*') ? 'show' : '' }}" id="sidebarDressTypes">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('dress-types.create') ? 'active' : '' }}"
                                    href="{{ route('dress-types.create') }}">
                                    Create
                                </a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('dress-types.index') ? 'active' : '' }}"
                                    href="{{ route('dress-types.index') }}">
                                    View
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endrole

            @role('super_admin|admin')
                <li class="nav-item {{ request()->routeIs('measurement-templates.*') ? 'section-active' : '' }}">
                    <a class="nav-link menu-arrow" href="#sidebarMeasurementTemplates" data-bs-toggle="collapse"
                        role="button"
                        aria-expanded="{{ request()->routeIs('measurement-templates.*') ? 'true' : 'false' }}"
                        aria-controls="sidebarMeasurementTemplates">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:ruler-angular-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Measure Templates</span>
                    </a>

                    <div class="collapse {{ request()->routeIs('measurement-templates.*') ? 'show' : '' }}"
                        id="sidebarMeasurementTemplates">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('measurement-templates.create') ? 'active' : '' }}"
                                    href="{{ route('measurement-templates.create') }}">
                                    Create
                                </a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('measurement-templates.index') ? 'active' : '' }}"
                                    href="{{ route('measurement-templates.index') }}">
                                    View
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endrole

            @role('super_admin|admin')
                <li class="nav-item {{ request()->routeIs('workflow-stages.*') ? 'section-active' : '' }}">
                    <a class="nav-link menu-arrow" href="#sidebarWorkflowStages" data-bs-toggle="collapse"
                        role="button" aria-expanded="{{ request()->routeIs('workflow-stages.*') ? 'true' : 'false' }}"
                        aria-controls="sidebarWorkflowStages">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:checklist-minimalistic-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Workflow Stages</span>
                    </a>

                    <div class="collapse {{ request()->routeIs('workflow-stages.*') ? 'show' : '' }}"
                        id="sidebarWorkflowStages">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('workflow-stages.create') ? 'active' : '' }}"
                                    href="{{ route('workflow-stages.create') }}">
                                    Create
                                </a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('workflow-stages.index') ? 'active' : '' }}"
                                    href="{{ route('workflow-stages.index') }}">
                                    View
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endrole

                 {{-- PROFILE (everyone) --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('admin.profile.edit') ? 'active' : '' }}"
                    href="{{ route('admin.profile.edit') }}">
                    <span class="nav-icon">
                        {{-- ✅ changed icon to a better "profile/settings" type --}}
                        <iconify-icon icon="solar:settings-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Profile </span>
                </a>
            </li>


            <div class="menu-divider"></div>

            {{-- =========================
                TAILORING SECTION
            ========================== --}}
            <li class="menu-title section-title">Tailoring Section</li>

            @role('super_admin|admin|front_desk')
                <li class="nav-item {{ $isTailoring ? 'section-active' : '' }}">
                    <a class="nav-link menu-arrow" href="#sidebarTailoring" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ $expandTailoring }}"
                        aria-controls="sidebarTailoring">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:clipboard-list-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Tailoring</span>
                    </a>

                    <div class="collapse {{ $isTailoring ? 'show' : '' }}" id="sidebarTailoring">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('tailoring.jobs.create') ? 'active' : '' }}"
                                    href="{{ route('tailoring.jobs.createWizard') }}">
                                    Create Job
                                </a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('tailoring.jobs.index') ? 'active' : '' }}"
                                    href="{{ route('tailoring.jobs.index') }}">
                                    View Jobs
                                </a>
                            </li>
                            {{-- <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('tailoring.reports.stages') ? 'active' : '' }}"
                                    href="{{ route('tailoring.reports.stages') }}">
                               Stage Report
                                </a>
                            </li> --}}
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('tailoring.reports.staff') ? 'active' : '' }}"
                                    href="{{ route('tailoring.reports.staff') }}">
                                    Staff Report
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
            @endrole

            <div class="menu-divider"></div>

            {{-- =========================
                HIRING SECTION
            ========================== --}}
            <li class="menu-title section-title">Hiring Section</li>

            @role('super_admin|admin|front_desk')
                <li class="nav-item {{ $isHiring ? 'section-active' : '' }}">
                    <a class="nav-link menu-arrow" href="#sidebarHiring" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ $expandHiring }}"
                        aria-controls="sidebarHiring">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:bag-3-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Hiring</span>
                    </a>

                    <div class="collapse {{ $isHiring ? 'show' : '' }}" id="sidebarHiring">
                        <ul class="nav sub-navbar-nav">

                            {{-- Hire Inventory --}}
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('hiring.items.create') ? 'active' : '' }}"
                                    href="{{ route('hiring.items.create') }}">
                                    Add Hire Item
                                </a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('hiring.items.index') ? 'active' : '' }}"
                                    href="{{ route('hiring.items.index') }}">
                                    View Hire Items
                                </a>
                            </li>

                            {{-- <li><hr class="dropdown-divider my-2"></li> --}}

                            {{-- Step 2 (we will implement next) --}}
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('hiring.agreements.create') ? 'active' : '' }}"
                                    href="{{ route('hiring.agreements.create') }}">
                                    Create Agreement
                                </a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('hiring.agreements.index') ? 'active' : '' }}"
                                    href="{{ route('hiring.agreements.index') }}">
                                    View Agreements
                                </a>
                            </li>

                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('hiring.reports.sales') ? 'active' : '' }}"
                                    href="{{ route('hiring.reports.sales') }}">
                                    Hiring Sales Report
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endrole

 

       
            {{-- @role('super_admin|admin|front_desk|cutter|sewing|button|ironing|packaging')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tailoring.handover.index') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:transfer-horizontal-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Handover </span>
                    </a>
                </li>
            @endrole --}}


            {{-- @role('super_admin|admin|front_desk|cutter|sewing|button|ironing|packaging')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tailoring.workqueue.index') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:clipboard-list-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Work Queue </span>
                    </a>
                </li>
            @endrole --}}

            {{-- @role('super_admin|admin|front_desk')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tailoring.delivery.index') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:bill-list-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Delivery & Invoice </span>
                    </a>
                </li>
            @endrole --}}

        </ul>
    </div>
</div>