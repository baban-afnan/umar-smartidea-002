<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="{{ route('dashboard') }}" class="logo logo-normal">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" style="height: 20px;">
        </a>
        <a href="{{ route('dashboard') }}" class="logo-small">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo">
        </a>
        <a href="{{ route('dashboard') }}" class="dark-logo">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo" style="height: 40px;">
        </a>
    </div>
    <!-- /Logo -->
    
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <!-- Main Menu -->
                <li class="menu-title"><span>Main Menu</span></li>
                
                <li @class(['active' => Request::is('dashboard')])>
                    <a href="{{ route('dashboard') }}" @class(['active' => Request::is('dashboard')])>
                        <i class="ti ti-smart-home"></i><span>Dashboard</span>
                    </a>
                </li>

                 <!-- Wallet-->
                <li class="submenu">
                    <a href="javascript:void(0);">
                        <i class="ti ti-receipt-2"></i>
                        <span>Wallet</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        <li><a href="{{ route('wallet') }}" class="{{ request()->routeIs('wallet') ? 'active' : '' }}">Wallet</a></li>
                        <li><a href="{{ route('admin.wallet.index') }}" class="{{ request()->routeIs('admin.wallet.index') ? 'active' : '' }}">Manual C/D</a></li>
                        <li><a href="{{ route('wallet.transfer') }}" class="{{ request()->routeIs('wallet.transfer') ? 'active' : '' }}">Balance Transfer</a></li>
                    </ul>
                </li>

                <!-- Services -->
                <li class="submenu">
                    <a href="javascript:void(0);">
                        <i class="ti ti-home-2"></i>
                        <span>Services</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        <li>
                          <a href="{{ route('admin.services.index') }}" class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}">Services</a>
                        </li>
                        <li><a href="{{ route('admin.data-variations.index') }}" class="{{ request()->routeIs('admin.data-variations.*') ? 'active' : '' }}">Data Services</a></li>
                        <li><a href="{{ route('admin.sme-data.index') }}" class="{{ request()->routeIs('admin.sme-data.index') ? 'active' : '' }}">SME Data</a></li>
                    </ul>
                </li>

                <!-- User management -->
                <li class="submenu {{ request()->routeIs('admin.users.*') ? 'active submenu-open' : '' }}">
                    <a href="javascript:void(0);" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="ti ti-users-group"></i>
                        <span>Users</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        <li><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}">Manage Users</a></li>
                        <li class="{{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                            <a href="{{ route('admin.notifications.index') }}" class="{{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">Notifications</a>
                        </li>
                    </ul>
                </li>

                <!-- Agency Services -->
                  <li class="submenu">
                    <a href="javascript:void(0);">
                        <i class="ti ti-credit-card"></i>
                        <span>BVN Services</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        <li><a href="{{ route('bvnmod.index') }}" class="{{ request()->routeIs('bvnmod.*') ? 'active' : '' }}">BVN Modification</a></li>
                        <li><a href="{{ route('ninmod.index') }}" class="{{ request()->routeIs('ninmod.*') ? 'active' : '' }}">NIN Modification</a></li>
                        <li><a href="{{ route('validation.index') }}" class="{{ request()->routeIs('validation.*') ? 'active' : '' }}">Validation</a></li>
                        <li><a href="{{ route('crm.index') }}" class="{{ request()->routeIs('crm.*') ? 'active' : '' }}">CRM</a></li>
                        <li><a href="{{ route('bvn-search.index') }}" class="{{ request()->routeIs('bvn-search.*') ? 'active' : '' }}">P/N Search</a></li>
                    </ul>
                </li>

                <!-- Verification -->
                  <li class="submenu">
                    <a href="javascript:void(0);">
                        <i class="ti ti-credit-card"></i>
                        <span>Other Services</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <ul>
                        <li><a href="{{ route('ninipe.index') }}" class="{{ request()->routeIs('ninipe.*') ? 'active' : '' }}">NIN IPE</a></li>
                        <li><a href="{{ route('vnin-nibss.index') }}" class="{{ request()->routeIs('vnin-nibss.*') ? 'active' : '' }}">VNIN to NIBSS</a></li>
                        <li><a href="{{ route('nin-personalisation.index') }}" class="{{ request()->routeIs('nin-personalisation.*') ? 'active' : '' }}">NIN Personalisation</a></li>
                    </ul>
                </li>

                <!-- Account Section -->
                <li class="menu-title"><span>Account</span></li>
                
                <li @class(['active' => Request::is('profile*')])>
                    <a href="{{ route('profile.edit') }}" @class(['active' => Request::is('profile*')])>
                        <i class="ti ti-settings-2"></i><span>Settings</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('transactions') }}" @class(['active' => Request::is('transactions*')])>
                        <i class="ti ti-history"></i><span>Transactions</span>
                    </a>
                </li>
                
                <li>
                    <a href="{{ route('support') }}" @class(['active' => Request::is('support*')])>
                        <i class="ti ti-headset"></i><span>Support</span>
                    </a>
                </li>
                
                <li>
                    <a href="#" onclick="confirmLogout(event, 'sidebar-logout-form')">
                        <i class="ti ti-logout"></i><span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->

<form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<style>
/* Clean Sidebar Styling - Green Theme */
.sidebar {
    background: #ffffff;
    border-right: 1px solid #f0f0f0;
    transition: all 0.3s ease;
    z-index: 1041;
}

@media (max-width: 991.98px) {
    .sidebar {
        margin-left: -252px; /* Hidden by default on mobile */
        width: 252px;
        position: fixed;
        top: 0;
        bottom: 0;
    }
    .slide-nav .sidebar {
        margin-left: 0 !important; /* Slide into view */
    }
}

.sidebar-logo {
    padding: 20px;
    background: #ffffff;
    border-bottom: 1px solid #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-menu {
    padding: 10px 0;
}

.sidebar-menu li a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    margin: 4px 15px;
    border-radius: 8px;
    color: #555;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
}

.sidebar-menu li a i {
    font-size: 1.2rem;
    margin-right: 12px;
    width: 24px;
    text-align: center;
}

.sidebar-menu li a:hover {
    background: rgba(13, 92, 62, 0.05);
    color: #0d5c3e;
}

/* Active Menu Item */
.sidebar-menu li.active > a,
.sidebar-menu li a.active {
    background: #0d5c3e !important;
    color: #6d2c2cff !important;
    box-shadow: 0 4px 12px rgba(13, 92, 62, 0.15);
}

.sidebar-menu li.active > a i,
.sidebar-menu li a.active i {
    color: #057c33ff !important;
}

/* Submenu Active Overrides */
.sidebar-menu .submenu ul li a.active {
    background: transparent !important;
    color: #0d5c3e !important;
    box-shadow: none !important;
    font-weight: 700;
}

.sidebar-menu .submenu ul li a.active:hover {
    text-decoration: underline;
}

/* Submenu Styles */
.sidebar-menu .submenu ul {
    display: none;
    background: #f9fafb;
    margin: 5px 15px;
    border-radius: 8px;
    list-style: none;
    padding: 5px 0;
}

.sidebar-menu .submenu.submenu-open > ul {
    display: block;
}

.sidebar-menu .submenu ul li a {
    padding-left: 45px;
    font-size: 0.85rem;
    margin: 2px 0;
    color: #666;
}

.sidebar-menu .submenu ul li a:hover {
    color: #0d5c3e;
    background: transparent;
    text-decoration: underline;
}

/* Menu Titles */
.menu-title {
    padding: 15px 25px 5px 25px;
    font-size: 10px;
    text-transform: uppercase;
    color: #851f1fff;
    font-weight: 700;
    letter-spacing: 1px;
}
}
</style>

<script>
function confirmLogout(event, formId) {
    event.preventDefault();
    
    Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out of your account.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0d5c3e',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
</script>