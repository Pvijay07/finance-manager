@extends('Admin.layouts.app')
@section('content')
<!-- User Management Page -->
<div id="user-management" class="page py-2">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-3">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px; border: 1px solid #e0e7ff;">
                    <i class="fas fa-users-gear fa-lg text-primary"></i>
                </div>
                <div>
                    <h1 style="font-weight: 800; color: #0f172a; font-size: 1.85rem; letter-spacing: -0.5px; margin: 0;">User Management</h1>
                    <p class="text-muted small mb-0 mt-0.5">Manage team credentials, role allocations, company authorizations, and system permissions.</p>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <!-- Search Bar -->
            <div class="search-container shadow-sm">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="userSearchInput" class="search-input"
                    placeholder="Search users by name, email, company..." autocomplete="off">
                <button type="button" class="clear-search-btn" id="clearUserSearch" style="display: none;" title="Clear search">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Filter Toggle Button -->
            <button class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 shadow-sm" onclick="toggleFilters()"
                style="border-radius: 10px; font-weight: 600; padding: 10px 16px; height: 42px; border-color: #cbd5e1; background: #fff;">
                <i class="fas fa-filter text-primary"></i> Filter
                <span id="filterActiveBadge" class="badge bg-primary text-white rounded-pill ms-1" style="display: none; font-size: 0.7rem;">Active</span>
            </button>

            <!-- Add User Button -->
            <button class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm" onclick="openAddUserModal()"
                style="border-radius: 10px; font-weight: 600; padding: 10px 18px; height: 42px;">
                <i class="fas fa-plus"></i> Add User
            </button>
        </div>
    </div>

    <!-- Summary Metrics Cards Row -->
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-4">
        <!-- Card 1: Total Users -->
        <div class="col">
            <div class="summary-card flex flex-col justify-between hover:border-primary transition-all">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="summary-header">
                        <p class="font-label-md text-label-md text-on-surface-variant mb-0 font-medium">Total Users</p>
                    </div>
                    <div class="p-sm bg-secondary-fixed rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined text-secondary">group</span>
                    </div>
                </div>
                <div class="summary-body">
                    <h4 class="font-headline-md text-headline-md text-primary mt-xs mb-2">{{ number_format($userStats['total'] ?? $users->count()) }}</h4>
                    <div class="mt-md d-flex align-items-center gap-sm">
                        <span class="font-data-mono text-data-mono text-on-surface-variant">System registered accounts</span>
                        <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="bg-secondary h-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Active Accounts -->
        <div class="col">
            <div class="summary-card flex flex-col justify-between hover:border-tertiary transition-all">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="summary-header">
                        <p class="font-label-md text-label-md text-on-surface-variant mb-0 font-medium">Active Accounts</p>
                    </div>
                    <div class="p-sm bg-tertiary-fixed rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined text-on-tertiary-fixed-variant" style="font-variation-settings: 'FILL' 1;">how_to_reg</span>
                    </div>
                </div>
                <div class="summary-body">
                    <h4 class="font-headline-md text-headline-md text-tertiary mt-xs mb-2">{{ number_format($userStats['active'] ?? $users->where('status', 'active')->count()) }}</h4>
                    <div class="mt-md d-flex align-items-center gap-sm">
                        <span class="font-data-mono text-data-mono text-on-surface-variant">
                            {{ ($userStats['total'] ?? $users->count()) > 0 ? round((($userStats['active'] ?? $users->where('status', 'active')->count()) / ($userStats['total'] ?? $users->count())) * 100) : 0 }}% active
                        </span>
                        <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="bg-tertiary h-full" style="width: {{ ($userStats['total'] ?? $users->count()) > 0 ? min(100, round((($userStats['active'] ?? $users->where('status', 'active')->count()) / ($userStats['total'] ?? $users->count())) * 100)) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Branch Managers -->
        <div class="col">
            <div class="summary-card flex flex-col justify-between hover:border-indigo-400 transition-all">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="summary-header">
                        <p class="font-label-md text-label-md text-on-surface-variant mb-0 font-medium">Branch Managers</p>
                    </div>
                    <div class="p-sm bg-secondary-fixed rounded-lg flex items-center justify-center" style="width: 40px; height: 40px;">
                        <span class="material-symbols-outlined text-secondary">supervisor_account</span>
                    </div>
                </div>
                <div class="summary-body">
                    <h4 class="font-headline-md text-headline-md text-primary mt-xs mb-2">{{ number_format($userStats['managers'] ?? $users->where('role', 'manager')->count()) }}</h4>
                    <div class="mt-md d-flex align-items-center gap-sm">
                        <span class="font-data-mono text-data-mono text-on-surface-variant">Company operational managers</span>
                        <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="bg-secondary h-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Admins & CAs -->
        <div class="col">
            <div class="summary-card flex flex-col justify-between hover:border-amber-400 transition-all">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="summary-header">
                        <p class="font-label-md text-label-md text-on-surface-variant mb-0 font-medium">Admins & CAs</p>
                    </div>
                    <div class="p-sm bg-amber-50 rounded-lg flex items-center justify-center" style="width: 40px; height: 40px; border: 1px solid #fef3c7;">
                        <span class="material-symbols-outlined text-amber-600">admin_panel_settings</span>
                    </div>
                </div>
                <div class="summary-body">
                    <h4 class="font-headline-md text-headline-md text-amber-600 mt-xs mb-2">{{ number_format($userStats['admins_ca'] ?? $users->whereIn('role', ['admin', 'ca'])->count()) }}</h4>
                    <div class="mt-md d-flex align-items-center gap-sm">
                        <span class="font-data-mono text-data-mono text-on-surface-variant">Privileged accounts</span>
                        <div class="h-1 flex-1 bg-surface-container-high rounded-full overflow-hidden">
                            <div class="bg-amber-500 h-full" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container shadow-sm border border-slate-200 rounded-2xl bg-white overflow-hidden mb-4">
        <!-- Modern Filter Toolbar inside Table Container -->
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3 bg-white">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="text-xs uppercase tracking-wider font-semibold text-slate-500 me-1" style="font-size: 0.75rem;">Role:</span>
                <button type="button" onclick="setRoleQuickFilter('')" class="role-filter-tab active" data-role="">
                    All <span class="tab-badge">{{ $users->count() }}</span>
                </button>
                <button type="button" onclick="setRoleQuickFilter('admin')" class="role-filter-tab" data-role="admin">
                    Administrators <span class="tab-badge">{{ $users->where('role', 'admin')->count() }}</span>
                </button>
                <button type="button" onclick="setRoleQuickFilter('manager')" class="role-filter-tab" data-role="manager">
                    Managers <span class="tab-badge">{{ $users->where('role', 'manager')->count() }}</span>
                </button>
                <button type="button" onclick="setRoleQuickFilter('ca')" class="role-filter-tab" data-role="ca">
                    CAs <span class="tab-badge">{{ $users->where('role', 'ca')->count() }}</span>
                </button>
                <button type="button" onclick="setRoleQuickFilter('user')" class="role-filter-tab" data-role="user">
                    Users <span class="tab-badge">{{ $users->where('role', 'user')->count() }}</span>
                </button>
            </div>

            <div id="usersCountDisplay" class="text-xs text-slate-500" style="font-size: 0.85rem;">
                Showing <strong>{{ $users->count() }}</strong> users
            </div>
        </div>

        <!-- Filter Row Drawer -->
        <div id="filterRow"
            style="display: none; background: #f8fafc; padding: 18px 20px; border-bottom: 1px solid #e2e8f0;">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select class="form-control" id="filterRole">
                        <option value="">All Roles</option>
                        <option value="admin">Administrator</option>
                        <option value="manager">Manager</option>
                        <option value="user">User</option>
                        <option value="ca">CA</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" id="filterStatus">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Company</label>
                    <select class="form-control" id="filterCompany">
                        <option value="">All Companies</option>
                        @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-outline-secondary w-100" onclick="resetFilters()" style="height: 38px; border-radius: 8px;">
                        <i class="fas fa-rotate me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table id="usersTable" class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Assigned Company</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    @php
                    $roleColors = [
                    'admin' => '#4f46e5',
                    'manager' => '#0284c7',
                    'ca' => '#d97706',
                    'user' => '#0d9488'
                    ];
                    $avatarBg = $roleColors[$user->role] ?? '#64748b';
                    @endphp
                    <tr data-role="{{ $user->role }}" data-status="{{ $user->status }}"
                        data-company="{{ $user->company_id }}">
                        <td>
                            <span class="badge-user-id">#{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="user-avatar-circle" style="background-color: {{ $avatarBg }};">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="d-flex flex-column">
                                    <strong class="text-slate-900 font-semibold" style="font-size: 0.92rem;">{{ $user->name }}</strong>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-slate-600" style="font-size: 0.88rem;">{{ $user->email }}</span>
                        </td>
                        <td>
                            <span class="role-badge role-{{ $user->role }}">
                                {{ ucfirst($user->role === 'ca' ? 'CA' : ($user->role === 'admin' ? 'Administrator' : $user->role)) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-1.5 text-slate-700">
                                <i class="fas fa-building text-slate-400" style="font-size: 0.8rem;"></i>
                                <span>{{ $user->company->name ?? 'All Companies' }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-pill {{ $user->status === 'active' ? 'status-pill-active' : 'status-pill-inactive' }}">
                                <span class="status-dot"></span>
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-slate-500" style="font-size: 0.85rem;">
                                @if ($user->last_login_at)
                                <i class="far fa-clock me-1 text-slate-400"></i>{{ $user->last_login_at->diffForHumans() }}
                                @else
                                <span class="text-muted italic">Never</span>
                                @endif
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons d-flex align-items-center gap-1.5">
                                <button class="btn btn-sm btn-outline-secondary btn-icon"
                                    onclick="changeStatus({{ $user->id }}, '{{ $user->status }}')"
                                    title="{{ $user->status === 'active' ? 'Deactivate account' : 'Activate account' }}">
                                    <i class="fas fa-{{ $user->status === 'active' ? 'ban text-warning' : 'check text-success' }}"></i>
                                </button>
                                @if ($user->id !== auth()->id())
                                <button class="btn btn-sm btn-outline-danger btn-icon"
                                    onclick="deleteUser({{ $user->id }})" title="Delete user">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Role Permissions Section -->
    <div class="card shadow-sm border border-slate-200 rounded-2xl bg-white overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom p-3.5 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2.5">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="fas fa-key fa-sm text-primary"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-semibold text-slate-900" style="font-size: 1.05rem;">Role Permissions & Access Control</h5>
                    <p class="text-muted small mb-0" style="font-size: 0.8rem;">Configure granular access rights and module privileges for each user role.</p>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Select Target Role</label>
                    <select class="form-control" id="role-select" onchange="loadRolePermissions()" style="height: 42px; border-radius: 8px;">
                        <option value="admin">Administrator</option>
                        <option value="manager">Manager</option>
                        <option value="user">User</option>
                        <option value="ca">CA</option>
                    </select>
                    <p class="text-muted small mt-2">
                        Select a role to inspect or update the baseline permission set applied across the platform.
                    </p>
                    <button class="btn btn-primary d-inline-flex align-items-center gap-2 mt-2 shadow-sm" onclick="saveRolePermissions()"
                        style="border-radius: 8px; font-weight: 600; padding: 9px 18px;">
                        <i class="fas fa-save"></i> Save Permissions
                    </button>
                </div>
                <div class="col-md-8">
                    <label class="form-label">Module Permissions</label>
                    <div id="permissions-container"
                        style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 18px; border-radius: 12px; max-height: 380px; overflow-y: auto;">
                        <!-- Permissions loaded via JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal" id="userModal"
    style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1050;">
    <div class="modal-content"
        style="background: white; width: 90%; max-width: 800px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
        <div class="modal-header"
            style="padding: 20px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
            <h5 class="modal-title" id="modalTitle" style="margin: 0; font-size: 1.25rem; font-weight: 600;">Add New
                User</h5>
            <button type="button" class="close" onclick="closeUserModal()"
                style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">
                <span>&times;</span>
            </button>
        </div>
        <form id="userForm">
            @csrf
            <input type="hidden" id="userId" name="id">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="user" selected>User</option>
                                <option value="admin">Administrator</option>
                                <option value="manager">Manager</option>
                                <option value="ca">CA</option>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label class="form-label">Assign Company</label>
                            <select class="form-control" id="company_id" name="company_id">
                                <option value="">All Companies</option>
                                @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group" id="customPermissionsContainer" style="display: none;">
                    <label class="form-label">Custom Permissions</label>
                    <div id="customPermissions"
                        style="max-height: 200px; overflow-y: auto; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        <!-- Custom permissions checkboxes -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                    onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">Save User</button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
    let currentUserId = null;
    const availablePermissions = @json($availablePermissions);
    let userModal = null;

    // Initialize Bootstrap modal and search
    document.addEventListener('DOMContentLoaded', function() {
        userModal = new bootstrap.Modal(document.getElementById('userModal'));
        loadRolePermissions();

        const searchInput = document.getElementById('userSearchInput');
        const clearBtn = document.getElementById('clearUserSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                if (clearBtn) clearBtn.style.display = this.value ? 'block' : 'none';
                filterUsers();
            });
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (searchInput) {
                    searchInput.value = '';
                    this.style.display = 'none';
                    filterUsers();
                }
            });
        }
    });

    function toggleFilters() {
        const filterRow = document.getElementById('filterRow');
        filterRow.style.display = filterRow.style.display === 'none' ? 'block' : 'none';
    }

    function setRoleQuickFilter(role) {
        document.getElementById('filterRole').value = role;
        document.querySelectorAll('.role-filter-tab').forEach(tab => {
            tab.classList.toggle('active', tab.getAttribute('data-role') === role);
        });
        filterUsers();
    }

    function resetFilters() {
        document.getElementById('filterRole').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterCompany').value = '';
        const searchInput = document.getElementById('userSearchInput');
        if (searchInput) {
            searchInput.value = '';
            const clearBtn = document.getElementById('clearUserSearch');
            if (clearBtn) clearBtn.style.display = 'none';
        }
        setRoleQuickFilter('');
    }

    function filterUsers() {
        const role = document.getElementById('filterRole').value;
        const status = document.getElementById('filterStatus').value;
        const company = document.getElementById('filterCompany').value;
        const searchVal = document.getElementById('userSearchInput') ? document.getElementById('userSearchInput').value.toLowerCase().trim() : '';

        // Update role quick tabs active state if role changed from dropdown
        document.querySelectorAll('.role-filter-tab').forEach(tab => {
            tab.classList.toggle('active', tab.getAttribute('data-role') === role);
        });

        // Update filter badge
        const filterBadge = document.getElementById('filterActiveBadge');
        if (filterBadge) {
            filterBadge.style.display = (status || company) ? 'inline-block' : 'none';
        }

        let visibleCount = 0;
        const rows = document.querySelectorAll('#usersTable tbody tr');
        rows.forEach(row => {
            let show = true;

            if (role && row.dataset.role !== role) show = false;
            if (status && row.dataset.status !== status) show = false;
            if (company && row.dataset.company !== company) show = false;
            if (searchVal) {
                const rowText = (row.innerText || '').toLowerCase();
                if (!rowText.includes(searchVal)) show = false;
            }

            row.style.display = show ? '' : 'none';
            if (show) visibleCount++;
        });

        const countDisplay = document.getElementById('usersCountDisplay');
        if (countDisplay) {
            countDisplay.innerHTML = `Showing <strong>${visibleCount}</strong> of ${rows.length} users`;
        }
    }

    // Event listeners for filters
    ['filterRole', 'filterStatus', 'filterCompany'].forEach(id => {
        document.getElementById(id).addEventListener('change', filterUsers);
    });

    function openAddUserModal() {
        document.getElementById('modalTitle').textContent = 'Add New User';
        document.getElementById('userForm').reset();
        document.getElementById('userId').value = '';
        document.getElementById('password').required = true;
        document.getElementById('password_confirmation').required = true;
        document.getElementById('customPermissionsContainer').style.display = 'none';

        // Reset role to User (default from screenshot)
        document.getElementById('role').value = 'user';

        // Show modal with flex display
        document.getElementById('userModal').style.display = 'flex';

        // Prevent body scrolling
        document.body.style.overflow = 'hidden';
    }

    function closeUserModal() {
        document.getElementById('userModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function editUser(userId) {
        fetch(`${window.APP_URL}/admin/users/${userId}/edit`)
            .then(response => response.json())
            .then(user => {
                console.log(user)
                document.getElementById('modalTitle').textContent = 'Edit User';
                document.getElementById('userId').value = user.id;
                document.getElementById('name').value = user.name;
                document.getElementById('email').value = user.email;
                document.getElementById('role').value = user.role;
                document.getElementById('company_id').value = user.company_id || '';
                document.getElementById('status').value = user.status;
                document.getElementById('password').required = false;
                document.getElementById('password_confirmation').required = false;

                // Clear password fields for edit mode
                document.getElementById('password').value = '';
                document.getElementById('password_confirmation').value = '';

                // Load custom permissions if any
                if (user.permissions && user.permissions.length > 0) {
                    loadCustomPermissions(user.role, user.permissions);
                } else {
                    document.getElementById('customPermissionsContainer').style.display = 'none';
                }

                // Show modal with flex display
                document.getElementById('userModal').style.display = 'flex';

                // Prevent body scrolling
                document.body.style.overflow = 'hidden';
            })
            .catch(error => {
                showNotification('error', 'Error loading user data');
                console.error(error);
            });
    }

    // Handle role change in modal
    document.getElementById('role').addEventListener('change', function() {
        const role = this.value;
        if (role === 'admin') {
            document.getElementById('customPermissionsContainer').style.display = 'none';
        } else {
            loadCustomPermissions(role);
        }
    });

    function loadCustomPermissions(role, selectedPermissions = []) {
        const container = document.getElementById('customPermissionsContainer');
        const permissionsDiv = document.getElementById('customPermissions');

        if (role === 'admin') {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        permissionsDiv.innerHTML = '';

        // Get role's default permissions
        fetch(`/users/role-permissions/${role}`)
            .then(response => response.json())
            .then(data => {
                const rolePermissions = data.permissions || [];

                // Combine with available permissions
                Object.keys(availablePermissions).forEach(module => {
                    const moduleDiv = document.createElement('div');
                    moduleDiv.className = 'mb-2';
                    moduleDiv.innerHTML = `<strong>${module.replace('_', ' ').toUpperCase()}</strong>`;

                    availablePermissions[module].forEach(perm => {
                        const isChecked = selectedPermissions.includes(perm.slug) ||
                            (selectedPermissions.length === 0 && rolePermissions.includes(perm
                                .slug));

                        const checkbox = document.createElement('div');
                        checkbox.innerHTML = `
                    <label style="display: block; margin-left: 20px;">
                        <input type="checkbox" name="permissions[]" value="${perm.slug}" 
                               ${isChecked ? 'checked' : ''}>
                        ${perm.name} - <small class="text-muted">${perm.description}</small>
                    </label>
                `;
                        moduleDiv.appendChild(checkbox);
                    });

                    permissionsDiv.appendChild(moduleDiv);
                });
            });
    }

    // Handle form submission
    document.getElementById('userForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const userId = document.getElementById('userId').value;
        const url = userId ? `${window.APP_URL}/admin/users/${userId}` :
            "{{ route('admin.users.store') }}";
        const method = userId ? 'PUT' : 'POST';
        const actionType = userId ? 'updated' : 'created';

        const permissions = [];
        document.querySelectorAll('input[name="permissions[]"]:checked').forEach(cb => {
            permissions.push(cb.value);
        });
        formData.set('permissions', JSON.stringify(permissions));

        fetch(url, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', data.message || `User ${actionType} successfully!`);
                    userModal.hide();

                    // Reload the page after successful operation
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    // Show error message
                    const errorMessage = data.message || 'Please check the form for errors';
                    showNotification('error', errorMessage);

                    // If there are validation errors, show them
                    if (data.errors) {
                        Object.values(data.errors).forEach(error => {
                            error.forEach(msg => {
                                showNotification('error', msg);
                            });
                        });
                    }
                }
            })
            .catch(error => {
                showNotification('error', 'Error saving user. Please try again.');
                console.error(error);
            });
    });

    function changeStatus(userId, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = newStatus === 'active' ? 'activated' : 'deactivated';

        if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this user?`)) {
            fetch(`${window.APP_URL}/admin/users/${userId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', `User ${action} successfully!`);
                        // Reload after a short delay
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showNotification('error', data.message || 'Failed to update user status');
                    }
                })
                .catch(error => {
                    showNotification('error', 'Error updating user status');
                    console.error(error);
                });
        }
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            fetch(`${window.APP_URL}/admin/users/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', 'User deleted successfully!');
                        // Reload after a short delay
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showNotification('error', data.message || 'Failed to delete user');
                    }
                })
                .catch(error => {
                    showNotification('error', 'Error deleting user');
                    console.error(error);
                });
        }
    }

    // Role Permissions Management
    function loadRolePermissions() {
        const role = document.getElementById('role-select').value;

        fetch(`${window.APP_URL}/admin/users/role-permissions/${role}`)
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('permissions-container');
                container.innerHTML = '';

                // Group permissions by module
                Object.keys(availablePermissions).forEach(module => {
                    const moduleDiv = document.createElement('div');
                    moduleDiv.className = 'mb-3';
                    moduleDiv.innerHTML = `<h6>${module.replace('_', ' ').toUpperCase()}</h6>`;

                    availablePermissions[module].forEach(perm => {
                        const isChecked = data.permissions.includes(perm.slug);

                        const permDiv = document.createElement('div');
                        permDiv.className = 'form-check';
                        permDiv.innerHTML = `
                    <input class="form-check-input" type="checkbox" 
                           id="perm-${perm.slug}" value="${perm.slug}" 
                           ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label" for="perm-${perm.slug}" 
                           style="font-size: 0.9rem; margin-left: 5px;">
                        <strong>${perm.name}</strong><br>
                        <small class="text-muted">${perm.description}</small>
                    </label>
                `;
                        moduleDiv.appendChild(permDiv);
                    });

                    container.appendChild(moduleDiv);
                });
            });
    }

    function showNotification(type, message) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.custom-notification');
        existingNotifications.forEach(notification => notification.remove());

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `custom-notification alert alert-${type === 'success' ? 'success' : 'danger'}`;
        notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000000;
        padding: 15px 20px;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        min-width: 300px;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    `;

        // Create notification content
        const icon = type === 'success' ?
            '<i class="fas fa-check-circle me-2"></i>' :
            '<i class="fas fa-exclamation-circle me-2"></i>';

        notification.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                ${icon}
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close btn-close-white" 
                    onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;

        // Add animation styles
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            .custom-notification {
                transition: all 0.3s ease;
            }
            .custom-notification.alert-success {
                background-color: #28a745;
                color: white;
                border: none;
            }
            .custom-notification.alert-danger {
                background-color: #dc3545;
                color: white;
                border: none;
            }
            .custom-notification .btn-close {
                filter: brightness(0) invert(1);
                opacity: 0.8;
            }
            .custom-notification .btn-close:hover {
                opacity: 1;
            }
        `;
            document.head.appendChild(style);
        }

        document.body.appendChild(notification);

        // Auto-remove after 5 seconds with animation
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOut 0.3s ease-out forwards';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 5000);
    }

    function saveRolePermissions() {
        const role = document.getElementById('role-select').value;
        const permissions = [];

        document.querySelectorAll('#permissions-container input[type="checkbox"]:checked').forEach(cb => {
            permissions.push(cb.value);
        });

        fetch(`${window.APP_URL}/admin/users/role-permissions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    role,
                    permissions
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('success', 'Role permissions saved successfully!');
                } else {
                    showNotification('error', 'Error saving permissions');
                }
            })
            .catch(error => {
                showNotification('error', 'Error saving permissions');
                console.error(error);
            });
    }
</script>

<style>
    .summary-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 18px;
        height: 100%;
        transition: all 0.25s ease-in-out;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .summary-card:hover {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        transform: translateY(-2px);
    }

    .summary-header {
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 8px;
        margin-bottom: 10px;
        width: 100%;
    }

    .search-container {
        position: relative;
        display: flex;
        align-items: center;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0 14px;
        min-width: 280px;
        height: 42px;
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-container:focus-within {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
    }

    .search-icon {
        color: #94a3b8;
        margin-right: 10px;
        font-size: 14px;
    }

    .search-input {
        border: none;
        outline: none;
        flex: 1;
        padding: 0;
        font-size: 0.88rem;
        background: transparent;
        width: 100%;
        height: 100%;
        color: #1e293b;
    }

    .search-input:focus {
        box-shadow: none;
    }

    .clear-search-btn {
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 4px;
        font-size: 12px;
        transition: color 0.2s;
        display: flex;
        align-items: center;
    }

    .clear-search-btn:hover {
        color: #ef4444;
    }

    /* Role Quick Filter Tabs */
    .role-filter-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        background: #f1f5f9;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
    }

    .role-filter-tab:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .role-filter-tab.active {
        background: #4f46e5;
        color: #ffffff;
        border-color: #4f46e5;
        box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
    }

    .role-filter-tab .tab-badge {
        background: rgba(0, 0, 0, 0.08);
        padding: 1px 7px;
        border-radius: 10px;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .role-filter-tab.active .tab-badge {
        background: rgba(255, 255, 255, 0.25);
        color: #ffffff;
    }

    /* User Monospaced ID */
    .badge-user-id {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        background: #f1f5f9;
        padding: 3px 7px;
        border-radius: 6px;
    }

    /* User Avatar Circle */
    .user-avatar-circle {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.88rem;
        color: #ffffff;
        flex-shrink: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Modern Role Badges */
    .role-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .role-admin {
        background-color: #e0e7ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
    }

    .role-manager {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }

    .role-ca {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .role-user {
        background-color: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
    }

    /* Modern Status Pills */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .status-pill .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-pill-active {
        background-color: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .status-pill-active .status-dot {
        background-color: #10b981;
    }

    .status-pill-inactive {
        background-color: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .status-pill-inactive .status-dot {
        background-color: #ef4444;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }

    .btn-icon:hover {
        transform: scale(1.05);
    }

    .form-label {
        font-weight: 600;
        font-size: 0.85rem;
        color: #334155;
        margin-bottom: 6px;
        display: block;
    }

    .required::after {
        content: " *";
        color: #dc3545;
    }

    #usersTable th {
        background-color: #f8fafc;
        font-weight: 600;
        font-size: 0.78rem;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
    }

    #usersTable td {
        padding: 14px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    #usersTable tr:hover td {
        background-color: #f8fafc;
    }
</style>
@endsection