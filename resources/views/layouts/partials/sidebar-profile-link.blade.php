<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.profile.edit') ? 'active' : '' }}"
        href="{{ route('admin.profile.edit') }}">
        <span class="nav-icon">
            <iconify-icon icon="solar:settings-outline"></iconify-icon>
        </span>
        <span class="nav-text"> Profile </span>
    </a>
</li>
