@extends('Admin.layouts.app')

@section('content')
    <meta name="companies-store-url" content="{{ route('admin.companies.store') }}">
    <meta name="companies-update-url" content="{{ route('admin.companies.update', ':id') }}">
    <meta name="companies-delete-url" content="{{ route('admin.companies.destroy', ':id') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="managers-data" content="{{ json_encode($managers) }}">

    <div id="company-management" class="page">
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">Company Management</div>
                <div class="table-actions">
                    <!-- Search Bar -->
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <form method="GET" action="{{ route('admin.companies') }}" id="search-form">
                            <input type="text" name="search" id="company-search" class="search-input"
                                placeholder="Search companies..." value="{{ request('search') }}" autocomplete="off">
                            @if (request('search'))
                                <button type="button" class="clear-search-btn" id="clear-search">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                        </form>
                    </div>

                    <!-- Add Company Button -->
                    <button class="btn btn-primary" id="add-company-btn">
                        <i class="fas fa-plus"></i> Add Company
                    </button>
                </div>
            </div>

            <!-- Status Filter -->
            <div class="filter-container">
                <div class="filter-buttons">
                    <a href="{{ route('admin.companies', array_merge(request()->except(['status', 'page']))) }}"
                        class="filter-btn {{ !request()->has('status') ? 'active' : '' }}">
                        All ({{ \App\Models\Company::count() }})
                    </a>
                    <a href="{{ route('admin.companies', array_merge(request()->except(['status', 'page']), ['status' => 'active'])) }}"
                        class="filter-btn {{ request('status') == 'active' ? 'active' : '' }}">
                        Active ({{ \App\Models\Company::where('status', 'active')->count() }})
                    </a>
                    <a href="{{ route('admin.companies', array_merge(request()->except(['status', 'page']), ['status' => 'inactive'])) }}"
                        class="filter-btn {{ request('status') == 'inactive' ? 'active' : '' }}">
                        Inactive ({{ \App\Models\Company::where('status', 'inactive')->count() }})
                    </a>
                </div>

                <!-- Results Count -->
                @if (request()->has('search') || request()->has('status'))
                    <div class="filter-results">
                        <span class="results-text">
                            {{ $companies->total() }} result(s) found
                            @if (request('search'))
                                for "{{ request('search') }}"
                            @endif
                        </span>
                        <a href="{{ route('admin.companies') }}" class="clear-filters-btn">
                            <i class="fas fa-times"></i> Clear filters
                        </a>
                    </div>
                @endif
            </div>

            <table>
                <thead>
                    <tr>
                        <th>
                            <a href="{{ route(
                                'admin.companies',
                                array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'name',
                                    'sort_order' => request('sort_by') == 'name' && request('sort_order') == 'asc' ? 'desc' : 'asc',
                                ]),
                            ) }}"
                                class="sortable-header">
                                Company Name
                                @if (request('sort_by') == 'name')
                                    <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort"></i>
                                @endif
                            </a>
                        </th>
                        <th>Manager</th>
                        <th>
                            <a href="{{ route(
                                'admin.companies',
                                array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'status',
                                    'sort_order' => request('sort_by') == 'status' && request('sort_order') == 'asc' ? 'desc' : 'asc',
                                ]),
                            ) }}"
                                class="sortable-header">
                                Status
                                @if (request('sort_by') == 'status')
                                    <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            <a href="{{ route(
                                'admin.companies',
                                array_merge(request()->except(['sort_by', 'sort_order', 'page']), [
                                    'sort_by' => 'created_at',
                                    'sort_order' => request('sort_by') == 'created_at' && request('sort_order') == 'asc' ? 'desc' : 'asc',
                                ]),
                            ) }}"
                                class="sortable-header">
                                Created Date
                                @if (request('sort_by') == 'created_at')
                                    <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort"></i>
                                @endif
                            </a>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        <tr data-company-id="{{ $company->id }}">
                            <td>
                                <div class="company-name">
                                    <strong>{{ $company->name }}</strong>
                                    @if (request('search'))
                                        @php
                                            $searchTerm = request('search');
                                            $highlightedName = preg_replace(
                                                '/(' . preg_quote($searchTerm, '/') . ')/i',
                                                '<span class="search-highlight">$1</span>',
                                                $company->name,
                                            );
                                        @endphp
                                        <div class="search-match">{!! $highlightedName !!}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if ($company->manager)
                                    <div class="manager-info">
                                        <span class="manager-name">{{ $company->manager->name }}</span>
                                        @if (request('search'))
                                            @php
                                                $highlightedManager = preg_replace(
                                                    '/(' . preg_quote(request('search'), '/') . ')/i',
                                                    '<span class="search-highlight">$1</span>',
                                                    $company->manager->name,
                                                );
                                            @endphp
                                            <div class="search-match">{!! $highlightedManager !!}</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="status {{ $company->status === 'active' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($company->status) }}
                                </span>
                            </td>
                            <td>{{ $company->created_at->format('d-M-y') }}</td>
                            <td>
                                <button class="btn btn-outline edit-company-btn" data-company-id="{{ $company->id }}"
                                    style="padding: 5px 10px; font-size: 0.8rem;">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger delete-company-btn" data-company-id="{{ $company->id }}"
                                    style="padding: 5px 10px; font-size: 0.8rem; margin-left: 5px;">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                @if (request()->has('search') || request()->has('status'))
                                    <div class="empty-state">
                                        <i class="fas fa-search fa-2x mb-3"></i>
                                        <h4>No companies found</h4>
                                        <p class="text-muted">
                                            @if (request('search'))
                                                No results for "{{ request('search') }}"
                                            @endif
                                            @if (request('status'))
                                                {{ request('status') == 'active' ? 'No active companies' : 'No inactive companies' }}
                                            @endif
                                        </p>
                                        <a href="{{ route('admin.companies') }}" class="btn btn-outline-primary">
                                            Clear filters
                                        </a>
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <i class="fas fa-building fa-2x mb-3"></i>
                                        <h4>No companies yet</h4>
                                        <p class="text-muted">Get started by creating your first company</p>
                                        <button class="btn btn-primary" id="add-company-btn-empty">
                                            <i class="fas fa-plus"></i> Add Company
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($companies->hasPages())
                <div class="pagination-container">
                    <div class="pagination-info">
                        Showing {{ $companies->firstItem() }} to {{ $companies->lastItem() }} of
                        {{ $companies->total() }} entries
                    </div>
                    <div class="pagination-links">
                        {{ $companies->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Company Modal -->
    <div class="modal" id="company-modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="modal-title">Add New Company</div>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <form id="company-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="company-id" name="id">
                <div style="padding: 20px;">

                    <!-- Company Name with validation feedback -->
                    <div class="form-group">
                        <div class="form-label-container">
                            <label class="form-label">Company Name <span class="required">*</span></label>
                            <span class="char-count" id="name-char-count">0/100</span>
                        </div>
                        <div class="input-with-icon">
                            <i class="fas fa-building input-icon"></i>
                            <input type="text" id="company-name" name="name" class="form-control"
                                placeholder="Enter company name" required maxlength="100" autocomplete="off">
                        </div>
                        <div class="error-message" id="name-error"></div>
                    </div>

                    <!-- Email with validation feedback -->
                    <div class="form-group">
                        <div class="form-label-container">
                            <label class="form-label">Email</label>
                            <span class="char-count" id="email-char-count">0/255</span>
                        </div>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" id="company-email" name="email" class="form-control"
                                placeholder="Enter company email" maxlength="255" autocomplete="off">
                        </div>
                        <div class="error-message" id="email-error"></div>
                    </div>

                    <!-- Manager Selection with search -->
                    <div class="form-group">
                        <label class="form-label">Assigned Manager</label>
                        <div class="select-with-icon">
                            <i class="fas fa-user-tie select-icon"></i>
                            <select class="form-control" name="manager_id" id="manager-select">
                                <option value="">Unassigned</option>
                                @foreach ($managers as $manager)
                                    <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                        <div class="hint-text">Select a manager to assign to this company</div>
                        <div class="error-message" id="manager_id-error"></div>
                    </div>

                    <!-- Two-column layout for Currency and Status -->
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label class="form-label">Default Currency <span class="required">*</span></label>
                            <div class="select-with-icon">
                                <i class="fas fa-money-bill-wave select-icon"></i>
                                <select class="form-control" name="currency" id="currency-select">
                                    <option value="INR">Indian Rupee (₹)</option>
                                    <option value="USD">US Dollar ($)</option>
                                    {{-- <option value="EUR">Euro (€)</option> --}}
                                </select>
                                <i class="fas fa-chevron-down select-arrow"></i>
                            </div>
                            <div class="error-message" id="currency-error"></div>
                        </div>

                        <div class="form-group half-width">
                            <label class="form-label">Status <span class="required">*</span></label>
                            <div class="select-with-icon">
                                <i class="fas fa-toggle-on select-icon"></i>
                                <select class="form-control" name="status" id="company-status-modal">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <i class="fas fa-chevron-down select-arrow"></i>
                            </div>
                            <div class="error-message" id="status-error"></div>
                        </div>
                    </div>

                    <!-- Website with preview hint -->
                    <div class="form-group">
                        <div class="form-label-container">
                            <label class="form-label">Website</label>
                            <span class="char-count" id="website-char-count">0/255</span>
                        </div>
                        <div class="input-with-icon">
                            <i class="fas fa-globe input-icon"></i>
                            <input type="text" id="company-website" name="website" class="form-control"
                                placeholder="https://example.com" maxlength="255" autocomplete="off">
                        </div>
                        <div class="hint-text">Include https:// for external links</div>
                        <div class="error-message" id="website-error"></div>
                    </div>

                    <!-- Address with character count -->
                    <div class="form-group">
                        <div class="form-label-container">
                            <label class="form-label">Address</label>
                            <span class="char-count" id="address-char-count">0/500</span>
                        </div>
                        <div class="textarea-with-icon">
                            <i class="fas fa-map-marker-alt textarea-icon"></i>
                            <textarea id="company-address" name="address" class="form-control" placeholder="Enter company address"
                                rows="3" maxlength="500"></textarea>
                        </div>
                        <div class="error-message" id="address-error"></div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-group"
                        style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                        <button type="button" class="btn btn-sm btn-warning close-modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" id="save-company-btn">
                            <span class="btn-text">Save Company</span>
                            <span class="btn-loading" style="display: none;">
                                <i class="spinner"></i> Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            const storeUrl = $('meta[name="companies-store-url"]').attr('content');
            const updateUrlTemplate = $('meta[name="companies-update-url"]').attr('content');
            const deleteUrlTemplate = $('meta[name="companies-delete-url"]').attr('content');
            const managers = JSON.parse($('meta[name="managers-data"]').attr('content'));

            // Modal Elements
            const companyModal = $('#company-modal');
            const deleteModal = $('#delete-modal');
            const modalTitle = $('#modal-title');
            const companyForm = $('#company-form');
            const companyIdInput = $('#company-id');

            // Track if form is being submitted to prevent double submission
            let isSubmitting = false;

            // Validation rules
            const validationRules = {
                name: {
                    required: true,
                    minLength: 2,
                    maxLength: 100,
                    pattern: /^[a-zA-Z0-9\s&.-]+$/,
                    messages: {
                        required: 'Company Name is required',
                        minLength: 'Company Name must be at least 2 characters',
                        maxLength: 'Company Name must not exceed 100 characters',
                        pattern: 'Company Name can only contain alphabets, numbers, spaces, &, -, .'
                    }
                },
                email: {
                    required: false,
                    maxLength: 100,
                    pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                    messages: {
                        invalid: 'Please enter a valid email address',
                        maxLength: 'Email must not exceed 100 characters'
                    }
                },
                manager_id: {
                    required: false,
                    messages: {
                        invalid: 'Invalid manager selection'
                    }
                },
                currency: {
                    required: true,
                    messages: {
                        required: 'Default Currency is required'
                    }
                },
                website: {
                    required: false,
                    maxLength: 150,
                    pattern: /^(https?:\/\/)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/,
                    messages: {
                        invalid: 'Please enter a valid website URL',
                        maxLength: 'Website must not exceed 150 characters'
                    }
                },
                address: {
                    required: false,
                    minLength: 5,
                    maxLength: 250,
                    messages: {
                        minLength: 'Address must be at least 5 characters',
                        maxLength: 'Address must not exceed 250 characters'
                    }
                },
                status: {
                    required: true,
                    allowedValues: ['active', 'inactive'],
                    messages: {
                        required: 'Status is required',
                        invalid: 'Invalid status value'
                    }
                }
            };

            // Add Company Button
            $('#add-company-btn').click(function() {
                companyForm[0].reset();
                companyIdInput.val('');
                modalTitle.text('Add New Company');
                clearErrorMessages();
                showModalCentered(companyModal);
            });

            // Edit Company Button
            $(document).on('click', '.edit-company-btn', function() {
                const companyId = $(this).data('company-id');
                loadCompanyData(companyId);
            });

            function showNotification(message, type = 'success') {
                // Remove any existing notifications
                $('.notification').remove();

                const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
                const bgColor = type === 'success' ? '#28a745' : '#dc3545';
                const borderColor = type === 'success' ? '#218838' : '#c82333';

                const notification = $(`
        <div class="notification ${type}">
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
            <button class="notification-close"><i class="fas fa-times"></i></button>
        </div>
    `);

                $('body').append(notification);

                // Add click handler for close button
                notification.find('.notification-close').click(function() {
                    notification.remove();
                });

                // Auto-remove after 5 seconds
                setTimeout(() => {
                    notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            // Clear all error messages
            function clearErrorMessages() {
                $('.error-message').text('').removeClass('show');
                $('.form-control').removeClass('error');
            }

            // Add error class to field
            function addErrorClass(fieldId) {
                $(`#${fieldId}, #company-${fieldId}, #${fieldId}-select, #manager-select, #currency-select, #company-status-modal`).addClass('error');
            }

            // Remove error class from field
            function removeErrorClass(fieldId) {
                $(`#${fieldId}, #company-${fieldId}, #${fieldId}-select, #manager-select, #currency-select, #company-status-modal`).removeClass('error');
            }

            // Validate single field
            function validateField(fieldName, value) {
                const rules = validationRules[fieldName];
                const errorElement = $(`#${fieldName}-error`);

                // Clear previous error
                errorElement.text('').removeClass('show');
                removeErrorClass(fieldName);

                // Skip validation if field is not required and empty
                if (!rules.required && (!value || value.toString().trim() === '')) {
                    return true;
                }

                let isValid = true;
                let errorMessage = '';

                // Check required field
                if (rules.required && (!value || value.toString().trim() === '')) {
                    isValid = false;
                    errorMessage = rules.messages.required;
                }
                // Check min length
                else if (rules.minLength && value.toString().trim().length < rules.minLength) {
                    isValid = false;
                    errorMessage = rules.messages.minLength;
                }
                // Check max length
                else if (rules.maxLength && value.toString().trim().length > rules.maxLength) {
                    isValid = false;
                    errorMessage = rules.messages.maxLength;
                }
                // Check pattern
                else if (rules.pattern && !rules.pattern.test(value.toString().trim())) {
                    isValid = false;
                    errorMessage = rules.messages.pattern || rules.messages.invalid || 'Invalid format';
                }
                // Check allowed values (for select fields)
                else if (rules.allowedValues && !rules.allowedValues.includes(value.toString().trim())) {
                    isValid = false;
                    errorMessage = rules.messages.invalid;
                }

                if (!isValid) {
                    errorElement.text(errorMessage).addClass('show');
                    addErrorClass(fieldName);
                }

                return isValid;
            }

            // Validate entire form
            function validateForm() {
                let isValid = true;

                // Validate name
                const name = $('#company-name').val();
                if (!validateField('name', name)) {
                    isValid = false;
                }

                // Validate email
                const email = $('#company-email').val();
                if (email && email.trim() !== '') {
                    if (!validateField('email', email)) {
                        isValid = false;
                    }
                }

                // Validate currency
                const currency = $('#currency-select').val();
                if (!validateField('currency', currency)) {
                    isValid = false;
                }

                // Validate website
                const website = $('#company-website').val();
                if (website && website.trim() !== '') {
                    if (!validateField('website', website)) {
                        isValid = false;
                    }
                }

                // Validate address
                const address = $('#company-address').val();
                if (address && address.trim() !== '') {
                    if (!validateField('address', address)) {
                        isValid = false;
                    }
                }

                // Validate status
                const status = $('#company-status-modal').val();
                if (!validateField('status', status)) {
                    isValid = false;
                }

                return isValid;
            }

            // Real-time character count update
            $(document).on('input', '.form-control', function() {
                const id = $(this).attr('id');
                const maxLength = $(this).attr('maxlength');
                if (id && maxLength) {
                    const charCountId = id.replace('company-', '') + '-char-count';
                    const currentLength = $(this).val().length;
                    $(`#${charCountId}`).text(`${currentLength}/${maxLength}`);
                }
            });

            // Real-time validation on input change
            $(document).on('blur', '.form-control', function() {
                const fieldName = $(this).attr('name');
                const value = $(this).val();

                if (fieldName && validationRules[fieldName]) {
                    validateField(fieldName, value);
                }
            });

            // Load Company Data for Edit
            function loadCompanyData(companyId) {
                $.ajax({
                    url: `https://xhtmlreviews.in/beta-finance/admin/companies/${companyId}/edit`,
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: function(response) {
                        if (response.success) {
                            const company = response.company;
                            companyIdInput.val(company.id);
                            $('#company-name').val(company.name);
                            $('#company-email').val(company.email);
                            $('#manager-select').val(company.manager_id);
                            $('#currency-select').val(company.currency);
                            $('#company-website').val(company.website);
                            $('#company-address').val(company.address);
                            $('#company-status-modal').val(company.status);

                            modalTitle.text('Edit Company');
                            clearErrorMessages();
                            showModalCentered(companyModal);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading company data:', xhr);
                        alert('Failed to load company data. Please try again.');
                    }
                });
            }

            // Save Company Button Click
            $('#save-company-btn').click(function(e) {
                e.preventDefault();
                handleCompanyFormSubmit();
            });

            // Also handle form submission via Enter key
            $('#company-form').on('submit', function(e) {
                e.preventDefault();
                handleCompanyFormSubmit();
            });

            function showModalCentered(modal) {
                modal.css({
                    'display': 'flex',
                    'align-items': 'center',
                    'justify-content': 'center'
                });
            }

            function handleCompanyFormSubmit() {
                if (isSubmitting) return;

                // Validate form
                if (!validateForm()) {
                    return;
                }

                isSubmitting = true;

                const companyId = companyIdInput.val();

                // Show loading
                const saveBtn = $('#save-company-btn');
                saveBtn.prop('disabled', true);
                saveBtn.find('.btn-text').hide();
                saveBtn.find('.btn-loading').show();

                // Create FormData object
                const formData = new FormData();

                // Manually add all form fields to FormData
                formData.append('_token', csrfToken);
                formData.append('name', $('#company-name').val());
                formData.append('email', $('#company-email').val());
                formData.append('manager_id', $('#manager-select').val());
                formData.append('currency', $('#currency-select').val());
                formData.append('website', $('#company-website').val());
                formData.append('address', $('#company-address').val());
                formData.append('status', $('#company-status-modal').val());

                if (companyId) {
                    formData.append('id', companyId);
                    // For UPDATE
                    const updateUrl = updateUrlTemplate.replace(':id', companyId);
                    formData.append('_method', 'PUT');

                    console.log('Update URL:', updateUrl);
                    console.log('Form Data for update:');
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`);
                    }

                    $.ajax({
                        url: updateUrl,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            console.log('Update success response:', response);
                            if (response.success) {
                                showNotification(response.message, 'success');

                                // Close modal with animation
                                setTimeout(() => {
                                    companyModal.hide();
                                    resetForm();
                                    location.reload();
                                }, 500);

                            } else {
                                showNotification(response.message ||
                                    `${isEditMode ? 'Update' : 'Create'} failed`, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(`${isEditMode ? 'Update' : 'Create'} error:`, {
                                xhr: xhr,
                                status: status,
                                error: error
                            });

                            // Handle timeout
                            if (status === 'timeout') {
                                showNotification('Request timed out. Please try again.', 'error');
                                return;
                            }

                            // Handle network errors
                            if (status === 'error' && !xhr.status) {
                                showNotification('Network error. Please check your connection.',
                                    'error');
                                return;
                            }

                            // Handle validation errors
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON?.errors;
                                console.log('Validation errors:', errors);

                                clearErrorMessages();

                                if (errors) {
                                    $.each(errors, function(field, messages) {
                                        const errorElement = $(`#${field}-error`);
                                        if (errorElement.length) {
                                            errorElement.text(messages[0]).addClass('show');
                                            addErrorClass(field);
                                        } else {
                                            // Show first error in notification
                                            showNotification(`${field}: ${messages[0]}`,
                                                'error');
                                            return;

                                        }
                                    });
                                } else {
                                    showNotification('Validation failed. Please check your input.',
                                        'error');
                                    return;

                                }
                            }
                            // Handle unauthorized
                            else if (xhr.status === 401) {
                                showNotification('Session expired. Please refresh the page.', 'error');
                                setTimeout(() => location.reload(), 2000);
                            }
                            // Handle forbidden
                            else if (xhr.status === 403) {
                                showNotification('You do not have permission to perform this action.',
                                    'error');
                            }
                            // Handle not found
                            else if (xhr.status === 404) {
                                showNotification('Resource not found. Please refresh the page.',
                                    'error');
                            }
                            // Handle server errors
                            else if (xhr.status >= 500) {
                                showNotification('Server error. Please try again later.', 'error');
                            }
                            // Handle other errors
                            else {
                                const errorMessage = xhr.responseJSON?.message ||
                                    xhr.statusText ||
                                    'An error occurred while processing your request';
                                showNotification(errorMessage, 'error');
                            }
                        },
                        complete: function() {
                            saveBtn.prop('disabled', false);
                            saveBtn.find('.btn-text').show();
                            saveBtn.find('.btn-loading').hide();
                            isSubmitting = false;
                        }
                    });
                } else {
                    // For CREATE
                    console.log('Form Data for create:');
                    for (let [key, value] of formData.entries()) {
                        console.log(`${key}: ${value}`);
                    }

                    $.ajax({
                        url: storeUrl,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        success: function(response) {
                            console.log('Create success response:', response);
                            if (response.success) {
                                showNotification(response.message, 'success');

                                // Close modal with animation
                                setTimeout(() => {
                                    companyModal.hide();
                                    resetForm();

                                    location.reload();
                                }, 500);

                            } else {
                                showNotification(response.message ||
                                    `${isEditMode ? 'Update' : 'Create'} failed`, 'error');
                            }
                        },
                        error: function(xhr) {
                            console.error('Create error:', xhr);
                            if (xhr.status === 422) {
                                const errors = xhr.responseJSON.errors;
                                console.log('Validation errors:', errors);
                                clearErrorMessages();
                                $.each(errors, function(field, messages) {
                                    const errorElement = $(`#${field}-error`);
                                    if (errorElement.length) {
                                        errorElement.text(messages[0]);
                                        addErrorClass(field);
                                    } else {
                                        alert(`${field}: ${messages[0]}`);
                                    }
                                });
                            } else {
                                alert(xhr.responseJSON?.message || 'An error occurred while creating');
                            }
                        },
                        complete: function() {
                            saveBtn.prop('disabled', false);
                            saveBtn.find('.btn-text').show();
                            saveBtn.find('.btn-loading').hide();
                            isSubmitting = false;
                        }
                    });
                }
            }

            // Confirm Delete using SweetAlert
            $('.delete-company-btn').on('click', function() {
                const companyId = $(this).data('company-id');
                const url = deleteUrlTemplate.replace(':id', companyId);

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This company will be permanently deleted!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#d33',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.ajax({
                            url: url,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        }).catch(xhr => {
                            Swal.showValidationMessage(
                                xhr.responseJSON?.message ||
                                'Failed to delete company'
                            );
                        });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value?.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: result.value.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            });

            // Close Modal
            $('.close-modal').click(function() {
                $(this).closest('.modal').hide();
                clearErrorMessages();
            });

            // Click outside to close
            $(window).click(function(e) {
                if ($(e.target).hasClass('modal')) {
                    $('.modal').hide();
                    clearErrorMessages();
                }
            });

            function resetForm() {
                $('#company-form')[0].reset();
                $('#company-id').val('');
                $('#modal-title').text('Add New Company');
                clearErrorMessages();
                removeErrorClasses();
            }

            function removeErrorClasses() {
                $('.form-control').removeClass('error success');
            }
            // Add CSS for notifications
            if (!$('#notification-styles').length) {
                $('head').append(`
        <style id="notification-styles">
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 12px;
                z-index: 9999;
                max-width: 400px;
                animation: slideInRight 0.3s ease-out;
                border-left: 4px solid;
                color: white;
                font-size: 14px;
            }
            
            .notification.success {
                background: #28a745;
                border-left-color: #218838;
            }
            
            .notification.error {
                background: #dc3545;
                border-left-color: #c82333;
            }
            
            .notification .fa-check-circle,
            .notification .fa-exclamation-circle {
                font-size: 16px;
            }
            
            .notification-close {
                background: none;
                border: none;
                color: white;
                cursor: pointer;
                opacity: 0.8;
                margin-left: auto;
                padding: 0;
                font-size: 12px;
                transition: opacity 0.2s;
            }
            
            .notification-close:hover {
                opacity: 1;
            }
            
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            /* Error and success states for form controls */
            .form-control.error {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
                animation: shake 0.3s ease-in-out;
            }
            
            .form-control.success {
                border-color: #28a745 !important;
                box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
            }
            
            .error-message.show {
                display: block !important;
                animation: fadeIn 0.3s ease-out;
            }
            
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-5px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
    `);
            }
        });
    </script>



    <style>
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .pagination-info {
            color: #666;
            font-size: 14px;
        }

        .pagination-links .pagination {
            margin: 0;
        }

        .pagination-links .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }

        .pagination-links .page-link {
            color: #007bff;
            border: 1px solid #dee2e6;
            padding: 6px 12px;
            font-size: 14px;
        }

        .pagination-links .page-link:hover {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        .pagination-links .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }
    </style>


    <style>
        .search-container {
            position: relative;
            display: flex;
            align-items: center;
            margin-right: 15px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px 10px;
            min-width: 250px;
        }

        #search-form {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .search-icon {
            color: #888;
            margin-right: 8px;
            font-size: 14px;
        }

        .search-input {
            border: none;
            outline: none;
            flex: 1;
            padding: 4px 0;
            font-size: 14px;
            background: transparent;
            width: 100%;
        }

        .search-input:focus {
            box-shadow: none;
        }

        .clear-search-btn {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            padding: 2px 5px;
            font-size: 12px;
            transition: color 0.3s;
        }

        .clear-search-btn:hover {
            color: #333;
        }

        .filter-container {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #eee;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .filter-btn {
            padding: 6px 15px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .filter-btn:hover {
            background: #e9ecef;
            border-color: #007bff;
            text-decoration: none;
            color: #333;
        }

        .filter-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .filter-results {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .results-text {
            color: #666;
            font-size: 14px;
        }

        .clear-filters-btn {
            color: #dc3545;
            font-size: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .clear-filters-btn:hover {
            text-decoration: underline;
        }

        .sortable-header {
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .sortable-header:hover {
            color: #007bff;
        }

        .search-highlight {
            background-color: #fff3cd;
            padding: 0 2px;
            border-radius: 2px;
            font-weight: bold;
        }

        .search-match {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state i {
            color: #6c757d;
            margin-bottom: 15px;
        }

        .empty-state h4 {
            margin-bottom: 10px;
            color: #333;
        }

        .table-actions {
            display: flex;
            align-items: center;
        }

        @media (max-width: 768px) {
            .table-actions {
                flex-direction: column;
                gap: 10px;
                align-items: stretch;
            }

            .search-container {
                margin-right: 0;
                min-width: auto;
            }

            .filter-buttons {
                flex-direction: column;
            }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('company-search');
            const clearSearchBtn = document.getElementById('clear-search');
            const searchForm = document.getElementById('search-form');
            const addCompanyBtnEmpty = document.getElementById('add-company-btn-empty');

            // Handle Enter key in search
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchForm.submit();
                }
            });

            // Handle clear search button
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    searchForm.submit();
                });
            }

            // Auto-submit search with debouncing (optional)
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                if (this.value.length === 0 || this.value.length >= 3) {
                    searchTimeout = setTimeout(() => {
                        searchForm.submit();
                    }, 500);
                }
            });

            // Handle empty state add button
            if (addCompanyBtnEmpty) {
                addCompanyBtnEmpty.addEventListener('click', function() {
                    document.getElementById('add-company-btn').click();
                });
            }
        });
    </script>
    <style>
        /* Modal Enhancements */
        .modal-content {
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            animation: modalSlideIn 0.3s ease-out;
        }

        .modal-header {
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
        }

        .close-modal {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 1.5rem;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .close-modal:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        /* Form Group Enhancements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        .required {
            color: #dc3545;
            margin-left: 2px;
        }

        .char-count {
            font-size: 12px;
            color: #6c757d;
            opacity: 0.7;
        }

        /* Input with Icons */
        .input-with-icon,
        .select-with-icon,
        .textarea-with-icon {
            position: relative;
        }

        .input-icon,
        .select-icon,
        .textarea-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 14px;
            z-index: 2;
        }

        .textarea-icon {
            top: 20px;
            transform: none;
        }

        .input-with-icon .form-control,
        .select-with-icon .form-control {
            padding-left: 40px;
        }

        .textarea-with-icon .form-control {
            padding-left: 40px;
        }

        .select-arrow {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            pointer-events: none;
            font-size: 12px;
        }

        /* Form Controls */
        .form-control {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 15px;
            font-size: 14px;
            transition: all 0.3s;
            background: #fff;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .form-control:hover {
            border-color: #adb5bd;
        }

        /* Two-column Layout */
        .form-row {
            display: flex;
            gap: 15px;
        }

        .half-width {
            flex: 1;
            min-width: 0;
        }

        /* Hint Text */
        .hint-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
            opacity: 0.8;
        }

        /* Error Message Styling */
        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
            min-height: 18px;
            display: none;
        }

        .error-message.show {
            display: block;
            animation: errorShake 0.3s ease-in-out;
        }

        /* Button Enhancements */
        .btn {
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-warning {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
            width: auto !important;
            height: auto !important;
        }

        .btn-warning:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        /* Loading Spinner */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }

        /* Success/Failure States */
        .form-control.success {
            border-color: #28a745;
        }

        .form-control.error {
            border-color: #dc3545;
            animation: errorShake 0.3s ease-in-out;
        }

        /* Animations */
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes errorShake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .modal-content {
                margin: 10px;
                width: calc(100% - 20px);
            }
        }
    /* Force action buttons to be visible by default (TC002) */
    .btn-group-sm .btn, 
    .table .btn-group .btn,
    .edit-company-btn,
    .delete-company-btn {
        opacity: 1 !important;
        visibility: visible !important;
        display: inline-block !important;
    }
    
    /* Ensure table rows don't hide buttons */
    tr .btn-group,
    tr td .btn {
        opacity: 1 !important;
        visibility: visible !important;
    }
    </style>
@endsection
