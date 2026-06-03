@extends('Admin.layouts.app')
@section('content')
<!-- System Settings Page -->
<div id="system-settings" class="page">
    <!-- Tabs for different setting categories -->
    <ul class="nav nav-tabs mb-4" id="settingsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">
                <i class="fas fa-cog me-2"></i> General
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button">
                <i class="fas fa-envelope me-2"></i> Email & Notifications
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="expense-tab" data-bs-toggle="tab" data-bs-target="#expense" type="button">
                <i class="fas fa-money-bill me-2"></i> Expense Categories
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button">
                <i class="fas fa-database me-2"></i> Backup & Maintenance
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="invoice-tab" data-bs-toggle="tab" data-bs-target="#invoice" type="button">
                <i class="fas fa-file-invoice me-2"></i> Invoice Settings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tax-tab" data-bs-toggle="tab" data-bs-target="#tax" type="button">
                <i class="fas fa-percentage me-2"></i> Tax Settings
            </button>
        </li>
    </ul>

    <div class="tab-content" id="settingsTabsContent">
        <!-- General Settings Tab -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">General Settings</div>
                </div>
                <form id="generalSettingsForm" method="POST" action="{{ route('admin.settings.save') }}">
                    @csrf
                    <input type="hidden" name="group" value="general">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Application Name *</label>
                                <input type="text" class="form-control" name="app_name"
                                    value="{{ $settings['app_name'] ?? 'Finance Manager' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default Currency *</label>
                                <select class="form-control" name="currency" required>
                                    <option value="₹"
                                        {{ ($settings['currency'] ?? '₹') == '₹' ? 'selected' : '' }}>Indian Rupee (₹)
                                    </option>
                                    <option value="$"
                                        {{ ($settings['currency'] ?? '₹') == '$' ? 'selected' : '' }}>US Dollar ($)
                                    </option>
                                    <option value="€"
                                        {{ ($settings['currency'] ?? '₹') == '€' ? 'selected' : '' }}>Euro (€)</option>
                                    <option value="£"
                                        {{ ($settings['currency'] ?? '₹') == '£' ? 'selected' : '' }}>British Pound (£)
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date Format *</label>
                                <select class="form-control" name="date_format" required>
                                    <option value="d/m/Y"
                                        {{ ($settings['date_format'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : '' }}>
                                        DD/MM/YYYY</option>
                                    <option value="m/d/Y"
                                        {{ ($settings['date_format'] ?? 'd/m/Y') == 'm/d/Y' ? 'selected' : '' }}>
                                        MM/DD/YYYY</option>
                                    <option value="Y-m-d"
                                        {{ ($settings['date_format'] ?? 'd/m/Y') == 'Y-m-d' ? 'selected' : '' }}>
                                        YYYY-MM-DD</option>
                                    <option value="d-M-Y"
                                        {{ ($settings['date_format'] ?? 'd/m/Y') == 'd-M-Y' ? 'selected' : '' }}>
                                        DD-MMM-YYYY</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time Format</label>
                                <select class="form-control" name="time_format">
                                    <option value="12"
                                        {{ ($settings['time_format'] ?? '12') == '12' ? 'selected' : '' }}>12 Hour
                                        (AM/PM)</option>
                                    <option value="24"
                                        {{ ($settings['time_format'] ?? '12') == '24' ? 'selected' : '' }}>24 Hour
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Session Timeout (minutes) *</label>
                                <input type="number" class="form-control" name="session_timeout"
                                    value="{{ $settings['session_timeout'] ?? '30' }}" min="5" max="240"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Timezone *</label>
                                <select class="form-control" name="timezone" required>
                                    <option value="Asia/Kolkata"
                                        {{ ($settings['timezone'] ?? 'Asia/Kolkata') == 'Asia/Kolkata' ? 'selected' : '' }}>
                                        Asia/Kolkata (IST)</option>
                                    <option value="UTC"
                                        {{ ($settings['timezone'] ?? 'Asia/Kolkata') == 'UTC' ? 'selected' : '' }}>UTC
                                    </option>
                                    <option value="America/New_York"
                                        {{ ($settings['timezone'] ?? 'Asia/Kolkata') == 'America/New_York' ? 'selected' : '' }}>
                                        America/New_York (EST)</option>
                                    <option value="Europe/London"
                                        {{ ($settings['timezone'] ?? 'Asia/Kolkata') == 'Europe/London' ? 'selected' : '' }}>
                                        Europe/London (GMT)</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Application Logo</label>
                                <div class="input-group">
                                    <input type="file" class="form-control" name="logo" accept="image/*">
                                    @if ($settings['logo_url'] ?? false)
                                    <span class="input-group-text">
                                        <img src="{{ $settings['logo_url'] }}" alt="Logo" height="30">
                                    </span>
                                    @endif
                                </div>
                                <small class="text-muted">Recommended: 200x60px PNG or JPG</small>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="enable_registration"
                                        id="enable_registration" value="1"
                                        {{ $settings['enable_registration'] ?? true ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_registration">
                                        Enable user registration
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="enable_maintenance"
                                        id="enable_maintenance" value="1"
                                        {{ $settings['enable_maintenance'] ?? false ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_maintenance">
                                        Enable maintenance mode
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save General Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Email & Notification Settings Tab -->
        <div class="tab-pane fade" id="email" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Email & Notification Settings</div>
                </div>
                <form id="emailSettingsForm" method="POST" action="{{ route('admin.settings.save') }}">
                    @csrf
                    <input type="hidden" name="group" value="email">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Mail Driver *</label>
                                <select class="form-control" name="mail_driver" required>
                                    <option value="smtp"
                                        {{ ($settings['mail_driver'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP
                                    </option>
                                    <option value="mailgun"
                                        {{ ($settings['mail_driver'] ?? 'smtp') == 'mailgun' ? 'selected' : '' }}>
                                        Mailgun</option>
                                    <option value="ses"
                                        {{ ($settings['mail_driver'] ?? 'smtp') == 'ses' ? 'selected' : '' }}>Amazon
                                        SES</option>
                                    <option value="sendmail"
                                        {{ ($settings['mail_driver'] ?? 'smtp') == 'sendmail' ? 'selected' : '' }}>
                                        Sendmail</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mail From Address *</label>
                                <input type="email" class="form-control" name="mail_from_address"
                                    value="{{ $settings['mail_from_address'] ?? 'noreply@example.com' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mail From Name *</label>
                                <input type="text" class="form-control" name="mail_from_name"
                                    value="{{ $settings['mail_from_name'] ?? 'Finance Manager' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" name="mail_host"
                                    value="{{ $settings['mail_host'] ?? 'smtp.mailtrap.io' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" name="mail_port"
                                    value="{{ $settings['mail_port'] ?? '587' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SMTP Username</label>
                                <input type="text" class="form-control" name="mail_username"
                                    value="{{ $settings['mail_username'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SMTP Password</label>
                                <input type="password" class="form-control" name="mail_password"
                                    value="{{ $settings['mail_password'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Encryption</label>
                                <select class="form-control" name="mail_encryption">
                                    <option value=""
                                        {{ ($settings['mail_encryption'] ?? 'tls') == '' ? 'selected' : '' }}>None
                                    </option>
                                    <option value="ssl"
                                        {{ ($settings['mail_encryption'] ?? 'tls') == 'ssl' ? 'selected' : '' }}>SSL
                                    </option>
                                    <option value="tls"
                                        {{ ($settings['mail_encryption'] ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-12 mt-4">
                                <h6>Notification Settings</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_invoice_due"
                                        id="notify_invoice_due" value="1"
                                        {{ $settings['notify_invoice_due'] ?? true ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notify_invoice_due">
                                        Send invoice due reminders
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_expense_due"
                                        id="notify_expense_due" value="1"
                                        {{ $settings['notify_expense_due'] ?? true ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notify_expense_due">
                                        Send expense due reminders
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_new_user"
                                        id="notify_new_user" value="1"
                                        {{ $settings['notify_new_user'] ?? true ? 'checked' : '' }}>
                                    <label class="form-check-label" for="notify_new_user">
                                        Notify on new user registration
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default Reminder Days *</label>
                                <input type="number" class="form-control" name="reminder_days"
                                    value="{{ $settings['reminder_days'] ?? '7' }}" min="1" max="30"
                                    required>
                                <small class="text-muted">Days before due date to send reminders</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Email Settings
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="testEmail()">
                            <i class="fas fa-paper-plane me-2"></i> Test Email
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Expense Categories Settings Tab -->
        <div class="tab-pane fade" id="expense" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-tags me-2 text-success"></i>Category Management
                    </div>
                </div>

                <div class="card-body">
                    <!-- Standard Fixed Expenses -->
                    <div class="card border-primary mb-4">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-lock me-2"></i> Standard Fixed Expenses
                            <span class="badge bg-light text-dark ms-2">{{ $standardFixed->count() }}</span>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Fixed expenses that appear automatically in each cycle
                            </p>

                            <form id="standardFixedForm" class="mb-3">
                                @csrf
                                <input type="hidden" name="type" value="standard_fixed">
                                <div id="standardFixedContainer">
                                    @foreach ($standardFixed as $index => $category)
                                    <div class="row g-2 mb-2 category-row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control"
                                                name="categories[{{ $index }}][name]"
                                                value="{{ $category->name }}" placeholder="Category Name"
                                                required>
                                        </div>
                                        <div class="col-md-2">
                                            @if (!$category->is_default)
                                            <button type="button"
                                                class="btn btn-danger w-100 remove-category-row"
                                                data-id="{{ $category->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <small class="badge bg-secondary">Default</small>
                                            @endif
                                        </div>
                                        <input type="hidden" name="categories[{{ $index }}][id]"
                                            value="{{ $category->id }}">
                                    </div>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="addCategoryRow('standard_fixed')">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success"
                                        onclick="saveCategories('standard_fixed')">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Standard Editable Expenses -->
                    <div class="card border-warning mb-4">
                        <div class="card-header bg-warning text-dark">
                            <i class="fas fa-edit me-2"></i> Standard Editable Expenses
                            <span class="badge bg-light text-dark ms-2">{{ $standardEditable->count() }}</span>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Templates that managers can modify in each cycle
                            </p>

                            <form id="standardEditableForm" class="mb-3">
                                @csrf
                                <input type="hidden" name="type" value="standard_editable">
                                <div id="standardEditableContainer">
                                    @foreach ($standardEditable as $index => $category)
                                    <div class="row g-2 mb-2 category-row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control"
                                                name="categories[{{ $index }}][name]"
                                                value="{{ $category->name }}" placeholder="Category Name"
                                                required>
                                        </div>
                                        <div class="col-md-2">
                                            @if (!$category->is_default)
                                            <button type="button"
                                                class="btn btn-danger w-100 remove-category-row"
                                                data-id="{{ $category->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <small class="badge bg-secondary">Default</small>
                                            @endif
                                        </div>
                                        <input type="hidden" name="categories[{{ $index }}][id]"
                                            value="{{ $category->id }}">
                                    </div>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                        onclick="addCategoryRow('standard_editable')">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success"
                                        onclick="saveCategories('standard_editable')">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Not Standard Expenses -->
                    <div class="card border-success mb-4">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-random me-2"></i> Not Standard Expenses
                            <span class="badge bg-light text-dark ms-2">{{ $notStandard->count() }}</span>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Ad-hoc variable expenses managers can create manually
                            </p>

                            <form id="notStandardForm" class="mb-3">
                                @csrf
                                <input type="hidden" name="type" value="not_standard">
                                <div id="notStandardContainer">
                                    @foreach ($notStandard as $index => $category)
                                    <div class="row g-2 mb-2 category-row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control"
                                                name="categories[{{ $index }}][name]"
                                                value="{{ $category->name }}" placeholder="Category Name"
                                                required>
                                        </div>
                                        <div class="col-md-2">
                                            @if (!$category->is_default)
                                            <button type="button"
                                                class="btn btn-danger w-100 remove-category-row"
                                                data-id="{{ $category->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <small class="badge bg-secondary">Default</small>
                                            @endif
                                        </div>
                                        <input type="hidden" name="categories[{{ $index }}][id]"
                                            value="{{ $category->id }}">
                                    </div>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-success"
                                        onclick="addCategoryRow('not_standard')">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success"
                                        onclick="saveCategories('not_standard')">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Income Categories -->
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-money-bill-wave me-2"></i> Income Categories
                            <span class="badge bg-light text-dark ms-2">{{ $income->count() }}</span>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Revenue sources and income categories
                            </p>

                            <form id="incomeForm" class="mb-3">
                                @csrf
                                <input type="hidden" name="type" value="income">
                                <div id="incomeContainer">
                                    @foreach ($income as $index => $category)
                                    <div class="row g-2 mb-2 category-row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control"
                                                name="categories[{{ $index }}][name]"
                                                value="{{ $category->name }}" placeholder="Category Name"
                                                required>
                                        </div>
                                        <div class="col-md-2">
                                            @if (!$category->is_default)
                                            <button type="button"
                                                class="btn btn-danger w-100 remove-category-row"
                                                data-id="{{ $category->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <small class="badge bg-secondary">Default</small>
                                            @endif
                                        </div>
                                        <input type="hidden" name="categories[{{ $index }}][id]"
                                            value="{{ $category->id }}">
                                    </div>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-outline-info"
                                        onclick="addCategoryRow('income')">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success"
                                        onclick="saveCategories('income')">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Backup & Maintenance Tab -->
        <div class="tab-pane fade" id="backup" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Backup & Maintenance</div>
                </div>
                <form id="backupSettingsForm" method="POST" action="{{ route('admin.settings.save') }}">
                    @csrf
                    <input type="hidden" name="group" value="backup">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Automatic Backup</label>
                                <select class="form-control" name="backup_frequency">
                                    <option value="disabled"
                                        {{ ($settings['backup_frequency'] ?? 'weekly') == 'disabled' ? 'selected' : '' }}>
                                        Disabled</option>
                                    <option value="daily"
                                        {{ ($settings['backup_frequency'] ?? 'weekly') == 'daily' ? 'selected' : '' }}>
                                        Daily</option>
                                    <option value="weekly"
                                        {{ ($settings['backup_frequency'] ?? 'weekly') == 'weekly' ? 'selected' : '' }}>
                                        Weekly</option>
                                    <option value="monthly"
                                        {{ ($settings['backup_frequency'] ?? 'weekly') == 'monthly' ? 'selected' : '' }}>
                                        Monthly</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Backup Retention (days)</label>
                                <input type="number" class="form-control" name="backup_retention"
                                    value="{{ $settings['backup_retention'] ?? '30' }}" min="1"
                                    max="365">
                                <small class="text-muted">Delete backups older than X days</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Backup Location</label>
                                <input type="text" class="form-control" name="backup_location"
                                    value="{{ $settings['backup_location'] ?? 'local' }}" readonly>
                                <small class="text-muted">Currently stored locally</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Last Backup</label>
                                <div class="alert alert-info">
                                    @if ($lastBackup)
                                    <div><strong>Date:</strong> {{ $lastBackup['date'] }}</div>
                                    <div><strong>Status:</strong>
                                        <span
                                            class="badge bg-{{ $lastBackup['status'] == 'success' ? 'success' : 'danger' }}">
                                            {{ ucfirst($lastBackup['status']) }}
                                        </span>
                                    </div>
                                    <div><strong>Size:</strong> {{ $lastBackup['size'] }}</div>
                                    <div><strong>File:</strong> {{ $lastBackup['filename'] }}</div>
                                    @else
                                    <div>No backups found</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Backup Settings
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="runBackup()">
                            <i class="fas fa-play me-2"></i> Run Backup Now
                        </button>
                        @if ($lastBackup)
                        <a href="{{ route('admin.settings.backup.download') }}" class="btn btn-outline-success">
                            <i class="fas fa-download me-2"></i> Download Latest Backup
                        </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Maintenance Tools -->
            <div class="card mt-4">
                <div class="card-header">
                    <div class="card-title">Maintenance Tools</div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-database fa-2x text-primary mb-3"></i>
                                    <h6>Clear Cache</h6>
                                    <p class="text-muted small">Clear application cache</p>
                                    <button class="btn btn-sm btn-outline-primary" onclick="clearCache()">
                                        Clear Cache
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-sync fa-2x text-warning mb-3"></i>
                                    <h6>Optimize Database</h6>
                                    <p class="text-muted small">Optimize database tables</p>
                                    <button class="btn btn-sm btn-outline-warning" onclick="optimizeDatabase()">
                                        Optimize
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <i class="fas fa-trash-alt fa-2x text-danger mb-3"></i>
                                    <h6>Clear Logs</h6>
                                    <p class="text-muted small">Clear old activity logs</p>
                                    <button class="btn btn-sm btn-outline-danger" onclick="clearLogs()">
                                        Clear Logs
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Settings Tab -->
        <div class="tab-pane fade" id="invoice" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Invoice Settings</div>
                </div>
                <form id="invoiceSettingsForm" method="POST" action="{{ route('admin.settings.save') }}">
                    @csrf
                    <input type="hidden" name="group" value="invoice">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Invoice Number Prefix *</label>
                                <input type="text" class="form-control" name="invoice_prefix"
                                    value="{{ $settings['invoice_prefix'] ?? 'INV' }}" required>
                                <small class="text-muted">e.g., INV for invoices, PRO for proformas</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Invoice Number Format *</label>
                                <select class="form-control" name="invoice_format" required>
                                    <option value="sequence"
                                        {{ ($settings['invoice_format'] ?? 'year_sequence') == 'sequence' ? 'selected' : '' }}>
                                        Sequential (001, 002)</option>
                                    <option value="year_sequence"
                                        {{ ($settings['invoice_format'] ?? 'year_sequence') == 'year_sequence' ? 'selected' : '' }}>
                                        Year-Sequence (2024-001)</option>
                                    <option value="prefix_year_sequence"
                                        {{ ($settings['invoice_format'] ?? 'year_sequence') == 'prefix_year_sequence' ? 'selected' : '' }}>
                                        Prefix-Year-Sequence (INV-2024-001)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default Payment Terms *</label>
                                <input type="number" class="form-control" name="payment_terms_days"
                                    value="{{ $settings['payment_terms_days'] ?? '30' }}" min="0"
                                    max="365" required>
                                <small class="text-muted">Days from invoice date</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Late Payment Fee (%)</label>
                                <input type="number" class="form-control" name="late_fee_percentage"
                                    value="{{ $settings['late_fee_percentage'] ?? '2' }}" min="0"
                                    max="100" step="0.01">
                                <small class="text-muted">Percentage of invoice amount</small>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Default Invoice Notes</label>
                                <textarea class="form-control" name="invoice_notes" rows="3">{{ $settings['invoice_notes'] ?? 'Payment is due within 30 days. Please include the invoice number with your payment.' }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Default Terms & Conditions</label>
                                <textarea class="form-control" name="invoice_terms" rows="4">{{ $settings['invoice_terms'] ??
                                        '1. All payments must be made in full within the specified due date.
                                    2. Late payments will incur a 2% monthly interest charge.
                                    3. Goods remain the property of the seller until paid in full.
                                    4. All disputes are subject to the jurisdiction of local courts.' }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="auto_generate_pdf"
                                        id="auto_generate_pdf" value="1"
                                        {{ $settings['auto_generate_pdf'] ?? true ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_generate_pdf">
                                        Auto-generate PDF on invoice creation
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="send_invoice_email"
                                        id="send_invoice_email" value="1"
                                        {{ $settings['send_invoice_email'] ?? true ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_invoice_email">
                                        Auto-send email on invoice creation
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Invoice Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tax Settings Tab -->
        <div class="tab-pane fade" id="tax" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Tax Settings</div>
                </div>
                <form id="taxSettingsForm" method="POST" action="{{ route('admin.settings.save') }}">
                    @csrf
                    <input type="hidden" name="group" value="tax">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Default GST Rate (%) *</label>
                                <input type="number" class="form-control" name="default_gst_rate"
                                    value="{{ $settings['default_gst_rate'] ?? '18' }}" min="0"
                                    max="100" step="0.01" required>
                                <small class="text-muted">Default GST percentage for invoices</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default TDS Rate (%) *</label>
                                <input type="number" class="form-control" name="default_tds_rate"
                                    value="{{ $settings['default_tds_rate'] ?? '10' }}" min="0"
                                    max="100" step="0.01" required>
                                <small class="text-muted">Default TDS percentage</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default Conversion Rate (%) *</label>
                                <input type="number" class="form-control" name="default_conversion_rate"
                                    value="{{ $settings['default_conversion_rate'] ?? '1.5' }}" min="0"
                                    max="100" step="0.01" required>
                                <small class="text-muted">Default currency conversion fee %</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default Base Rate (1 USD to INR) *</label>
                                <input type="number" class="form-control" name="default_base_rate"
                                    value="{{ $settings['default_base_rate'] ?? '83.00' }}" min="0"
                                    step="0.01" required>
                                <small class="text-muted">Default conversion rate for USD to INR</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tax Calculation Method *</label>
                                <select class="form-control" name="tax_calculation_method" required>
                                    <option value="inclusive"
                                        {{ ($settings['tax_calculation_method'] ?? 'exclusive') == 'inclusive' ? 'selected' : '' }}>
                                        Tax Inclusive</option>
                                    <option value="exclusive"
                                        {{ ($settings['tax_calculation_method'] ?? 'exclusive') == 'exclusive' ? 'selected' : '' }}>
                                        Tax Exclusive</option>
                                </select>
                                <small class="text-muted">Inclusive: Tax included in price, Exclusive: Tax added to
                                    subtotal</small>
                            </div>
                            <div class="col-md-12">
                                <h6>Additional Tax Rates</h6>
                                <div id="taxRatesContainer">
                                    @foreach ($taxRates as $rate)
                                    <div class="row g-2 mb-2 tax-rate-row">
                                        <div class="col-md-5">
                                            <input type="text" class="form-control"
                                                name="tax_rates[{{ $loop->index }}][name]"
                                                value="{{ $rate['name'] ?? '' }}"
                                                placeholder="Tax Name (e.g., CGST)">
                                        </div>
                                        <div class="col-md-5">
                                            <input type="number" class="form-control"
                                                name="tax_rates[{{ $loop->index }}][rate]"
                                                value="{{ $rate['rate'] ?? '' }}" step="0.01" min="0"
                                                max="100" placeholder="Rate (%)">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-danger w-100"
                                                onclick="removeTaxRate(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                                    onclick="addTaxRate()">
                                    <i class="fas fa-plus"></i> Add Tax Rate
                                </button>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company GSTIN</label>
                                <input type="text" class="form-control" name="company_gstin"
                                    value="{{ $settings['company_gstin'] ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company PAN</label>
                                <input type="text" class="form-control" name="company_pan"
                                    value="{{ $settings['company_pan'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Tax Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createCategoryForm" method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i> Create New Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Category Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Main Type *</label>
                            <select class="form-control" name="main_type" required
                                onchange="updateSubTypes(this.value)">
                                <option value="">Select Type</option>
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sub Type *</label>
                            <select class="form-control" name="sub_type" required id="subTypeSelect">
                                <option value="">Select Main Type First</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i> Edit Category
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Category Name *</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Main Type *</label>
                            <select class="form-control" name="main_type" required>
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sub Type *</label>
                            <select class="form-control" name="sub_type" required>
                                <option value="standard">Standard Fixed</option>
                                <option value="editable">Standard Editable</option>
                                <option value="variable">Not Standard</option>
                                <option value="regular">Income</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        let taxRateCount = {
            {
                count($taxRates)
            }
        };
        let fixedExpenseCount = {
            {
                count($fixedExpenses ?? [])
            }
        };
        let editableExpenseCount = {
            {
                count($editableExpenses ?? [])
            }
        };
        let variableCategoryCount = {
            {
                count($variableCategories ?? [])
            }
        };

        // Test Email Function
        window.testEmail = function() {
            if (confirm('Send test email to admin email?')) {
                fetch('{{ route('admin.settings.test-email') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            }
                        })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Test email sent successfully!');
                        } else {
                            alert('Error sending test email: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error sending test email');
                    });
            }
        };

        // Add Fixed Expense

        // Add Tax Rate
        window.addTaxRate = function() {
            const container = document.getElementById('taxRatesContainer');
            const newRow = document.createElement('div');
            newRow.className = 'row g-2 mb-2 tax-rate-row';
            newRow.innerHTML = `
                    <div class="col-md-5">
                        <input type="text" class="form-control" name="tax_rates[${taxRateCount}][name]" 
                               placeholder="Tax Name (e.g., CGST)">
                    </div>
                    <div class="col-md-5">
                        <input type="number" class="form-control" name="tax_rates[${taxRateCount}][rate]" 
                               step="0.01" min="0" max="100" placeholder="Rate (%)">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger w-100" onclick="removeTaxRate(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            container.appendChild(newRow);
            taxRateCount++;
        };

        // Remove Tax Rate
        window.removeTaxRate = function(button) {
            if (document.querySelectorAll('.tax-rate-row').length > 1) {
                button.closest('.tax-rate-row').remove();
            } else {
                alert('At least one tax rate is required.');
            }
        };

        // Run Backup
        window.runBackup = function() {
            if (confirm('Start backup process? This may take a few minutes.')) {
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Backing up...';
                btn.disabled = true;

                fetch('{{ route('admin.settings.backup.run') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Backup completed successfully!');
                            location.reload();
                        } else {
                            alert('Backup failed: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error during backup');
                    })
                    .finally(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    });
            }
        };

        // Maintenance Functions
        window.clearCache = function() {
            if (confirm('Clear application cache?')) {
                fetch('{{ route('admin.settings.clear-cache') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error clearing cache');
                    });
            }
        };

        window.optimizeDatabase = function() {
            if (confirm('Optimize database tables?')) {
                fetch('{{ route('admin.settings.optimize-db') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error optimizing database');
                    });
            }
        };

        window.clearLogs = function() {
            if (confirm('Clear old activity logs? This cannot be undone.')) {
                const days = prompt('Delete logs older than (days):', '30');
                if (days) {
                    fetch('{{ route('admin.settings.clear-logs') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    days: days
                                })
                            })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error clearing logs');
                        });
                }
            }
        };

        // Form submission handling for expense settings
        document.getElementById('expenseSettingsForm')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            submitBtn.disabled = true;

            fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        // Update counts from response
                        fixedExpenseCount = data.fixedExpenseCount || fixedExpenseCount;
                        editableExpenseCount = data.editableExpenseCount || editableExpenseCount;
                        variableCategoryCount = data.variableCategoryCount || variableCategoryCount;
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving expense settings');
                })
                .finally(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Form submission handling for other forms
        document.querySelectorAll('form[id$="SettingsForm"]:not(#expenseSettingsForm)').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
                submitBtn.disabled = true;

                fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error saving settings');
                    })
                    .finally(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
            });
        });
    });
</script>



<script>
    // Add category row
    function addCategoryRow(type) {
        // Map type to container ID
        const typeMap = {
            'standard_fixed': 'standardFixedContainer',
            'standard_editable': 'standardEditableContainer',
            'not_standard': 'notStandardContainer',
            'income': 'incomeContainer'
        };

        const containerId = typeMap[type];
        if (!containerId) {
            console.error('Invalid type:', type);
            return;
        }

        const container = document.getElementById(containerId);
        if (!container) {
            console.error('Container not found:', containerId);
            return;
        }

        // Count existing rows in this container
        const rows = container.querySelectorAll('.category-row');
        const rowCount = rows.length;

        // Create new row
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 mb-2 category-row';
        newRow.innerHTML = `
            <div class="col-md-5">
                <input type="text" class="form-control" 
                       name="categories[${rowCount}][name]" 
                       placeholder="New Category Name" required>
            </div>
          
            <div class="col-md-2">
                <button type="button" class="btn btn-danger w-100 remove-category-row">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <input type="hidden" name="categories[${rowCount}][id]" value="">
        `;

        // Add row to container
        container.appendChild(newRow);

        // Scroll to the new row
        newRow.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });

        // Focus on the name input
        const nameInput = newRow.querySelector('input[name^="categories"]');
        if (nameInput) {
            setTimeout(() => nameInput.focus(), 100);
        }

        showToast('info', `Added new row to ${getTypeLabel(type)}`);
    }

    // Get human-readable type label
    function getTypeLabel(type) {
        const labels = {
            'standard_fixed': 'Standard Fixed Expenses',
            'standard_editable': 'Standard Editable Expenses',
            'not_standard': 'Not Standard Expenses',
            'income': 'Income Categories'
        };
        return labels[type] || type;
    }

    // Save categories - Fixed version
    function saveCategories(type) {
        const typeMap = {
            'standard_fixed': 'standardFixedForm',
            'standard_editable': 'standardEditableForm',
            'not_standard': 'notStandardForm',
            'income': 'incomeForm'
        };

        const formId = typeMap[type];
        if (!formId) {
            showToast('error', 'Invalid category type');
            return;
        }

        const form = document.getElementById(formId);
        if (!form) {
            showToast('error', `Form not found: ${formId}`);
            return;
        }

        // Get all category rows
        const containerId = formId.replace('Form', 'Container');
        const container = document.getElementById(containerId);
        if (!container) {
            showToast('error', `Container not found: ${containerId}`);
            return;
        }

        const rows = container.querySelectorAll('.category-row');
        const categories = [];

        rows.forEach((row, index) => {
            const nameInput = row.querySelector('input[name^="categories"]');
            // const descriptionInput = row.querySelector('textarea[name^="categories"]');
            const idInput = row.querySelector('input[type="hidden"]');

            if (nameInput && nameInput.value.trim()) {
                const category = {
                    id: idInput ? idInput.value : '',
                    name: nameInput.value.trim(),
                    // description: descriptionInput ? descriptionInput.value.trim() : ''
                };
                categories.push(category);
            }
        });

        if (categories.length === 0) {
            showToast('warning', 'Please add at least one category');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Show loading
        const saveBtn = form.querySelector('button[onclick^="saveCategories"]');
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
        saveBtn.disabled = true;

        fetch('{{ route('admin.categories.bulk-update') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: type,
                        categories: categories
                    })
                })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast('success', data.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast('error', data.message || 'Failed to save categories');
                    saveBtn.innerHTML = originalText;
                    saveBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Error saving categories: ' + error.message);
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            });
    }

    // Remove category row with confirmation - Fixed version
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-category-row')) {
            const button = e.target.closest('.remove-category-row');
            const row = button.closest('.category-row');
            const categoryId = button.dataset.id;

            if (categoryId) {
                // This is an existing category, ask for confirmation
                if (confirm('Are you sure you want to delete this category?')) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch(`https://xhtmlreviews.in/beta-finance/admin/categories/${categoryId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('success', data.message);
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showToast('error', data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('error', 'Error deleting category');
                        });
                }
            } else {
                // This is a new row that hasn't been saved yet
                row.remove();
                showToast('info', 'Unsaved row removed');

                // Re-index remaining rows
                reindexCategoryRows(row.closest('[id$="Container"]'));
            }
        }
    });

    // Re-index category rows after removal
    function reindexCategoryRows(container) {
        if (!container) return;

        const rows = container.querySelectorAll('.category-row');
        rows.forEach((row, index) => {
            // Update input names
            const nameInput = row.querySelector('input[name^="categories"]');
            // const descriptionInput = row.querySelector('textarea[name^="categories"]');
            const idInput = row.querySelector('input[type="hidden"]');

            if (nameInput) {
                nameInput.name = `categories[${index}][name]`;
            }
            // if (descriptionInput) {
            //     descriptionInput.name = `categories[${index}][description]`;
            // }
            if (idInput) {
                idInput.name = `categories[${index}][id]`;
            }
        });
    }

    // Toast notification - Improved version
    function showToast(type, message) {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.custom-toast');
        existingToasts.forEach(toast => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        });

        const toast = document.createElement('div');
        toast.className = `custom-toast alert alert-${type} alert-dismissible fade show`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
            animation: slideInRight 0.3s ease;
        `;

        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;

        document.body.appendChild(toast);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }

    // Test the function in console
    console.log('Category management functions loaded');
    console.log('Available types:', ['standard_fixed', 'standard_editable', 'not_standard', 'income']);
</script>
@endsection