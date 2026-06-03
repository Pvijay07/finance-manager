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

            <table id="usersTable">
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
                                <button class="btn btn-outline btn-sm" onclick="editUser({{ $user->id }})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-outline btn-sm"
                                    onclick="changeStatus({{ $user->id }}, '{{ $user->status }}')">
                                    <i class="fas fa-{{ $user->status === 'active' ? 'ban' : 'check' }}"></i>
                                </button>
                                @if ($user->id !== auth()->id())
                                    <button class="btn btn-outline btn-sm text-danger"
                                        onclick="deleteUser({{ $user->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Role Permissions</div>
            </div>
            <div style="padding: 20px;">
                <div class="form-group">
                    <label class="form-label">Select Role</label>
                    <select class="form-control" id="role-select" onchange="loadRolePermissions()">
                        <option value="admin">Administrator</option>
                        <option value="manager">Manager</option>
                        <option value="user">User</option>
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
    <div class="modal fade" id="userModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add New User</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="userForm">
                    @csrf
                    <input type="hidden" id="userId" name="id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label>Password <span id="passwordNote" class="text-muted small">(leave blank to keep
                                    unchanged)</span></label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" class="form-control" id="password_confirmation"
                                name="password_confirmation">
                        </div>
                        <div class="form-group">
                            <label>Role *</label>
                            <select class="form-control" id="role" name="role" required>
                                <option value="admin">Administrator</option>
                                <option value="manager">Manager</option>
                                <option value="user" selected>User</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Assign Company</label>
                            <select class="form-control" id="company_id" name="company_id">
                                <option value="">All Companies</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status *</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="form-group" id="customPermissionsContainer" style="display: none;">
                            <label>Custom Permissions</label>
                            <div id="customPermissions"
                                style="max-height: 200px; overflow-y: auto; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                                <!-- Custom permissions checkboxes -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        let currentUserId = null;
        const availablePermissions = @json($availablePermissions);

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
            document.getElementById('passwordNote').textContent = '(minimum 8 characters)';
            document.getElementById('customPermissionsContainer').style.display = 'none';
            $('#userModal').modal('show');
        }

        function editUser(userId) {
            fetch(`https://xhtmlreviews.in/beta-finance/admin/users/${userId}/edit`)
                .then(response => response.json())
                .then(user => {
                    document.getElementById('modalTitle').textContent = 'Edit User';
                    document.getElementById('userId').value = user.id;
                    document.getElementById('name').value = user.name;
                    document.getElementById('email').value = user.email;
                    document.getElementById('role').value = user.role;
                    document.getElementById('company_id').value = user.company_id || '';
                    document.getElementById('status').value = user.status;
                    document.getElementById('password').required = false;
                    document.getElementById('password_confirmation').required = false;
                    document.getElementById('passwordNote').textContent = '(leave blank to keep unchanged)';

                    // Load custom permissions if any
                    if (user.permissions && user.permissions.length > 0) {
                        loadCustomPermissions(user.role, user.permissions);
                    } else {
                        document.getElementById('customPermissionsContainer').style.display = 'none';
                    }

                    $('#userModal').modal('show');
                })
                .catch(error => {
                    alert('Error loading user data');
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
                        moduleDiv.innerHTML = `<strong>${module.toUpperCase()}</strong>`;

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

            const permissions = [];
            document.querySelectorAll('input[name="permissions[]"]:checked').forEach(cb => {
                permissions.push(cb.value);
            });
            formData.set('permissions', JSON.stringify(permissions));

            fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        $('#userModal').modal('hide');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        alert('Error: ' + (data.message || 'Please check the form'));
                    }
                })
                .catch(error => {
                    alert('Error saving user');
                    console.error(error);
                });
        });

        function changeStatus(userId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

            if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this user?`)) {
                fetch(`https://xhtmlreviews.in/beta-finance/admin/users/${userId}/status`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        alert('Error updating status');
                        console.error(error);
                    });
            }
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                fetch(`/users/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        alert('Error deleting user');
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
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        role,
                        permissions
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Permissions saved successfully!');
                    } else {
                        alert('Error saving permissions');
                    }
                })
                .catch(error => {
                    alert('Error saving permissions');
                    console.error(error);
                });
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadRolePermissions();
        });
    </script>

    <style>
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
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
        }

        .status.active {
            background-color: #d4edda;
            color: #155724;
        }

        .status.inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
@endsection
