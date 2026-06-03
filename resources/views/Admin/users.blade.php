@extends('Admin.layouts.app')
@section('content')
    <!-- User Management Page -->
    <div id="user-management" class="page">
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">User Management</div>
                <div class="table-actions">
                    <button class="btn btn-outline" onclick="toggleFilters()">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button class="btn btn-primary" onclick="openAddUserModal()">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
            </div>

            <!-- Filter Row -->
            <div id="filterRow"
                style="display: none; background: #f8f9fa; padding: 15px; margin-bottom: 15px; border-radius: 5px;">
                <div class="row">
                    <div class="col-md-3">
                        <label>Role</label>
                        <select class="form-control" id="filterRole">
                            <option value="">All Roles</option>
                            <option value="admin">Administrator</option>
                            <option value="manager">Manager</option>
                            <option value="user">User</option>
                            <option value="ca">CA</option>

                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Status</label>
                        <select class="form-control" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Company</label>
                        <select class="form-control" id="filterCompany">
                            <option value="">All Companies</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-secondary" onclick="resetFilters()">Reset</button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="usersTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
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
                            <tr data-role="{{ $user->role }}" data-status="{{ $user->status }}"
                                data-company="{{ $user->company_id }}">
                                <td>#{{ str_pad($user->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{!! $user->role_badge !!}</td>
                                <td>{{ $user->company->name ?? 'All Companies' }}</td>
                                <td>
                                    <span class="status {{ $user->status === 'active' ? 'active' : 'inactive' }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($user->last_login_at)
                                        {{ $user->last_login_at->diffForHumans() }}
                                    @else
                                        Never
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        {{-- <button class="btn btn-icon btn-sm" onclick="editUser({{ $user->id }})"
                                            title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button> --}}
                                        <button class="btn btn-icon btn-sm"
                                            onclick="changeStatus({{ $user->id }}, '{{ $user->status }}')"
                                            title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $user->status === 'active' ? 'ban' : 'check' }}"></i>
                                        </button>
                                        @if ($user->id !== auth()->id())
                                            <button class="btn btn-icon btn-sm text-danger"
                                                onclick="deleteUser({{ $user->id }})" title="Delete">
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

        <div class="card mt-4">
            <div class="card-header">
                <div class="card-title">Role Permissions</div>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Select Role</label>
                    <select class="form-control" id="role-select" onchange="loadRolePermissions()">
                        <option value="admin">Administrator</option>
                        <option value="manager">Manager</option>
                        <option value="user">User</option>
                            <option value="ca">CA</option>

                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Permissions</label>
                    <div id="permissions-container"
                        style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; max-height: 300px; overflow-y: auto;">
                        <!-- Permissions will be loaded here -->
                    </div>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" onclick="saveRolePermissions()">
                        <i class="fas fa-save"></i> Save Permissions
                    </button>
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

        // Initialize Bootstrap modal
        document.addEventListener('DOMContentLoaded', function() {
            userModal = new bootstrap.Modal(document.getElementById('userModal'));
            loadRolePermissions();
        });

        function toggleFilters() {
            const filterRow = document.getElementById('filterRow');
            filterRow.style.display = filterRow.style.display === 'none' ? 'block' : 'none';
        }

        function resetFilters() {
            document.getElementById('filterRole').value = '';
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterCompany').value = '';
            filterUsers();
        }

        function filterUsers() {
            const role = document.getElementById('filterRole').value;
            const status = document.getElementById('filterStatus').value;
            const company = document.getElementById('filterCompany').value;

            document.querySelectorAll('#usersTable tbody tr').forEach(row => {
                let show = true;

                if (role && row.dataset.role !== role) show = false;
                if (status && row.dataset.status !== status) show = false;
                if (company && row.dataset.company !== company) show = false;

                row.style.display = show ? '' : 'none';
            });
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
            fetch(`https://xhtmlreviews.in/beta-finance/admin/users/${userId}/edit`)
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
            const url = userId ? `https://xhtmlreviews.in/beta-finance/admin/users/${userId}` :
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
                fetch(`https://xhtmlreviews.in/beta-finance/admin/users/${userId}/status`, {
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
                fetch(`https://xhtmlreviews.in/beta-finance/admin/users/${userId}`, {
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

            fetch(`https://xhtmlreviews.in/beta-finance/admin/users/role-permissions/${role}`)
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

            fetch('https://xhtmlreviews.in/beta-finance/admin/users/role-permissions', {
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

    {{-- <style>
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .role-admin {
            background-color: #dc3545;
            color: white;
        }

        .role-manager {
            background-color: #ffc107;
            color: #212529;
        }

        .role-user {
            background-color: #28a745;
            color: white;
        }

        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
        }

        .status.active {
            background-color: #d4edda;
            color: #155724;
        }

        .status.inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-icon {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
        }

        .required::after {
            content: " *";
            color: #dc3545;
        }

        #userModal .modal-body .row {
            margin-bottom: 10px;
        }

        #usersTable th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style> --}}
@endsection
