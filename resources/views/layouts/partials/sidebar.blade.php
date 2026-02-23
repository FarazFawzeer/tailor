<div class="app-sidebar">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="{{ route('any', 'index') }}" class="logo-dark">
            <img src="/images/logo.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo.png" class="logo-lg" alt="logo dark" style="width: 150px; height: 120px;">
        </a>

        <a href="{{ route('any', 'index') }}" class="logo-light">
            <img src="/images/logo.png" class="logo-sm" alt="logo sm">
            <img src="/images/logo.png" class="logo-lg" alt="logo light" style="width: 150px; height: 120px;">
        </a>
    </div>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">
            <li class="menu-title">Menu...</li>

            {{-- ADMIN (only super_admin/admin) --}}
            @role('super_admin|admin')
                <li class="nav-item">
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
                <li class="nav-item">
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
                <li class="nav-item">
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
                <li class="nav-item">
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
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarMeasurementTemplates" data-bs-toggle="collapse"
                        role="button"
                        aria-expanded="{{ request()->routeIs('measurement-templates.*') ? 'true' : 'false' }}"
                        aria-controls="sidebarMeasurementTemplates">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:ruler-angular-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Measurement Templates</span>
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
                <li class="nav-item">
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

            @role('super_admin|admin|front_desk')
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarTailoring" data-bs-toggle="collapse" role="button"
                        aria-expanded="{{ request()->is('tailoring/*') ? 'true' : 'false' }}"
                        aria-controls="sidebarTailoring">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:clipboard-list-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Tailoring</span>
                    </a>

                    <div class="collapse {{ request()->is('tailoring/*') ? 'show' : '' }}" id="sidebarTailoring">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('tailoring.jobs.create') ? 'active' : '' }}"
                                    href="{{ route('tailoring.jobs.create') }}">
                                    Create Job
                                </a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link {{ request()->routeIs('tailoring.jobs.index') ? 'active' : '' }}"
                                    href="{{ route('tailoring.jobs.index') }}">
                                    View Jobs
                                </a>
                            </li>

                        </ul>
                    </div>
                </li>
            @endrole

            @role('super_admin|admin|front_desk')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tailoring.production.dashboard') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:chart-square-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Production Dashboard </span>
                    </a>
                </li>
            @endrole

            @role('super_admin|admin|front_desk|cutter|sewing|button|ironing|packaging')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tailoring.handover.index') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:transfer-horizontal-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Handover </span>
                    </a>
                </li>
            @endrole


            @role('super_admin|admin|front_desk|cutter|sewing|button|ironing|packaging')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tailoring.workqueue.index') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:clipboard-list-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Work Queue </span>
                    </a>
                </li>
            @endrole

            @role('super_admin|admin|front_desk')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('tailoring.delivery.index') }}">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:bill-list-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Delivery & Invoice </span>
                    </a>
                </li>
            @endrole

            @role('super_admin|admin|front_desk')
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarHiring" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarHiring">
                        <span class="nav-icon">
                            <iconify-icon icon="solar:bag-3-outline"></iconify-icon>
                        </span>
                        <span class="nav-text"> Hiring</span>
                    </a>

                    <div class="collapse" id="sidebarHiring">
                        <ul class="nav sub-navbar-nav">

                            {{-- Hire Inventory --}}
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('hiring.items.create') }}">Add Hire Item</a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('hiring.items.index') }}">View Hire Items</a>
                            </li>

                            {{-- <li><hr class="dropdown-divider my-2"></li> --}}

                            {{-- Step 2 (we will implement next) --}}
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('hiring.agreements.create') }}">Create
                                    Agreement</a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('hiring.agreements.index') }}">View Agreements</a>
                            </li>

                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('hiring.availability.index') }}">Availability
                                    Dashboard</a>
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
                        <iconify-icon icon="solar:widget-2-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Profile </span>
                </a>
            </li>

        </ul>
    </div>
</div>
