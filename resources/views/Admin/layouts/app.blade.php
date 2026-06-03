<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Admin - Accounting Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('public/assets/styles.css') }}">

</head>
<style>
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        margin: 0;
    }

    /* Premium Layout & Typography */
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

    body {
        background-color: #f4f7f6;
        font-family: 'Outfit', sans-serif;
        color: #1e293b;
        -webkit-font-smoothing: antialiased;
    }

    /* Glassmorphism Sidebar */
    .sidebar {
        background: linear-gradient(145deg, #1e293b, #0f172a) !important;
        box-shadow: 4px 0 25px rgba(0, 0, 0, 0.1);
        color: white !important;
        border-right: 1px solid rgba(255, 255, 255, 0.05);
    }

    .sidebar-header {
        background: transparent !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        padding: 25px 20px !important;
    }

    .sidebar-header h2 {
        font-weight: 700;
        letter-spacing: 0.5px;
        background: linear-gradient(to right, #60a5fa, #c084fc);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1.4rem !important;
    }

    .sidebar-header i {
        color: #60a5fa;
        font-size: 1.5rem;
    }

    .sidebar-menu {
        padding: 20px 10px !important;
    }

    /* Premium Sidebar Links */
    .menu-item {
        border-radius: 12px;
        margin-bottom: 8px;
        padding: 14px 20px !important;
        color: rgba(255, 255, 255, 0.7) !important;
        font-weight: 500;
        letter-spacing: 0.3px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border-left: none !important;
    }

    .menu-item i {
        color: rgba(255, 255, 255, 0.5);
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .menu-item:hover {
        background: rgba(255, 255, 255, 0.06) !important;
        color: #ffffff !important;
        transform: translateX(5px);
    }

    .menu-item:hover i {
        color: #60a5fa;
        transform: scale(1.1);
    }

    .menu-item.active {
        background: linear-gradient(90deg, rgba(59, 130, 246, 0.2), transparent) !important;
        color: #ffffff !important;
        position: relative;
    }

    .menu-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        height: 60%;
        width: 4px;
        background: #3b82f6;
        border-radius: 0 4px 4px 0;
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
    }

    .menu-item.active i {
        color: #3b82f6;
    }

    /* Beautiful Header */
    .header {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
        padding: 20px 30px !important;
        border-radius: 0 0 20px 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        margin-bottom: 30px !important;
        position: sticky;
        top: 0;
        z-index: 50;
    }

    .header #page-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 1.5rem;
        letter-spacing: -0.5px;
    }

    /* Modern User Profile area */
    .user-info {
        background: #f8fafc;
        padding: 8px 16px 8px 8px;
        border-radius: 50px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .user-info:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border-color: #cbd5e1;
    }

    .user-avatar {
        background: linear-gradient(135deg, #3b82f6, #8b5cf6) !important;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        font-weight: 700 !important;
        font-family: 'Outfit', sans-serif;
        letter-spacing: 1px;
        width: 42px !important;
        height: 42px !important;
    }

    .user-info>div>div:first-child {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .user-info>div>div:nth-child(2) {
        color: #64748b !important;
        font-weight: 500;
    }

    /* Custom minimal scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Main Content Area adjustments */
    .main-content {
        background-color: transparent !important;
        padding: 0 30px 30px !important;
    }

    /* ========================================================================= */
    /* PREMIUM GLOBAL FORMS & TABLES UI/UX                                       */
    /* ========================================================================= */

    /* General Card Design */
    .card {
        border: none !important;
        border-radius: 20px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04) !important;
        background: #ffffff !important;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .card-header {
        background: transparent !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 24px 32px !important;
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
    }

    .card-body {
        padding: 32px !important;
    }

    /* Enhanced Forms Inputs */
    .form-label,
    label {
        font-weight: 600;
        color: #475569;
        margin-bottom: 10px;
        font-size: 0.95rem;
        letter-spacing: 0.3px;
    }

    .form-control,
    .form-select,
    select.form-control,
    input[type="text"],
    input[type="number"],
    input[type="email"],
    input[type="password"] {
        border-radius: 12px !important;
        border: 2px solid #e2e8f0 !important;
        padding: 12px 16px !important;
        transition: all 0.25s ease !important;
        background-color: #f8fafc !important;
        color: #1e293b !important;
        font-weight: 500;
        box-shadow: none !important;
    }

    .form-control:focus,
    .form-select:focus,
    select.form-control:focus,
    input:focus {
        background-color: #ffffff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
        outline: none !important;
    }

    /* Fix for overlapping icons in inputs */
    .input-with-icon .form-control,
    .select-with-icon .form-control,
    .textarea-with-icon .form-control {
        padding-left: 45px !important;
    }

    .input-with-icon,
    .select-with-icon,
    .textarea-with-icon {
        position: relative;
    }

    .input-with-icon .input-icon,
    .select-with-icon .select-icon,
    .textarea-with-icon .textarea-icon {
        color: #64748b !important;
        font-size: 1.1rem;
        z-index: 5;
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .textarea-with-icon .textarea-icon {
        top: 16px;
        transform: none;
    }

    /* Global Buttons Overhaul */
    .btn {
        border-radius: 10px !important;
        padding: 10px 20px !important;
        font-weight: 600 !important;
        letter-spacing: 0.4px;
        transition: all 0.3s ease !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #2563eb, #3b82f6) !important;
        border: none !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
    }

    .btn-primary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4) !important;
        background: linear-gradient(135deg, #1d4ed8, #2563eb) !important;
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981, #34d399) !important;
        border: none !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3) !important;
    }

    .btn-success:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4) !important;
    }

    .btn-danger {
        background: linear-gradient(135deg, #ef4444, #f87171) !important;
        border: none !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3) !important;
    }

    .btn-danger:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4) !important;
    }

    /* Premium Tailwind-inspired Tables */
    .table-responsive {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        margin-top: 15px;
    }

    .table {
        margin-bottom: 0 !important;
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table th,
    .table td {
        border-top: none;
        border-bottom: 1px solid #f1f5f9;
    }

    .table th {
        background-color: #f8fafc !important;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 16px 20px !important;
        border-bottom: 2px solid #e2e8f0 !important;
        vertical-align: middle;
    }

    .table td {
        padding: 18px 20px !important;
        vertical-align: middle;
        color: #1e293b;
        font-weight: 500;
        transition: background-color 0.2s ease;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: #f8fafc !important;
        transform: scale(1.002);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Custom Checkboxes */
    .form-check-input {
        width: 1.25em !important;
        height: 1.25em !important;
        border: 2px solid #cbd5e1 !important;
        cursor: pointer;
        box-shadow: none !important;
    }

    .form-check-input:checked {
        background-color: #3b82f6 !important;
        border-color: #3b82f6 !important;
    }

    .form-check-label {
        cursor: pointer;
        font-weight: 500;
        color: #334155;
        margin-left: 6px;
    }

    /* Input Groups (e.g., currency symbols prefix) */
    .input-group-text {
        border-radius: 12px !important;
        background-color: #f1f5f9 !important;
        border: 2px solid #e2e8f0 !important;
        border-right: none !important;
        color: #64748b !important;
        font-weight: 600 !important;
        padding: 0 18px !important;
    }

    .input-group>.form-control {
        border-left: none !important;
        padding-left: 0 !important;
    }

    .input-group:focus-within .input-group-text {
        border-color: #3b82f6 !important;
    }

    /* Badges */
    .badge {
        padding: 6px 12px !important;
        border-radius: 50px !important;
        font-weight: 600 !important;
        letter-spacing: 0.3px !important;
    }
    /* Force action buttons and tabs to be visible by default (TC002, TC004-TC007) */
    .btn-edit, 
    .btn-delete, 
    .btn-outline, 
    .btn-outline-secondary,
    .tab-button,
    .edit-company-btn,
    .delete-company-btn,
    .edit-expense-btn,
    .delete-expense-btn,
    .btn-group .btn,
    .table .btn,
    tr .btn-group,
    tr td .btn,
    .btn[onclick*="edit"],
    .btn[onclick*="delete"],
    .btn[onclick*="cancel"] {
        opacity: 1 !important;
        visibility: visible !important;
        display: inline-block !important;
    }

    /* Ensure tabs are always visible and clickable */
    .tabs-header, .tabs-container {
        opacity: 1 !important;
        visibility: visible !important;
        display: flex !important;
    }
</style>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-cogs"></i>
                <h2>Finance Admin</h2>
            </div>
            <div class="sidebar-menu">
                <a href="{{ route('admin.dashboard') }}"
                    class="menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-page="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('admin.companies') }}"
                    class="menu-item {{ request()->routeIs('admin.companies') ? 'active' : '' }}"
                    data-page="company-management">
                    <i class="fas fa-building"></i>
                    <span>Company Management</span>
                </a>

                <a href="{{ route('admin.standard-expenses') }}"
                    class="menu-item {{ request()->routeIs('admin.standard-expenses') ? 'active' : '' }}"
                    data-page="standard-expenses">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Standard Expenses</span>
                </a>
                <a href="{{ route('admin.invoices') }}"
                    class="menu-item {{ request()->routeIs('admin.invoices') ? 'active' : '' }}"
                    data-page="standard-expenses">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Standard Income</span>
                </a>
                <a href="{{ route('admin.users') }}"
                    class="menu-item {{ request()->routeIs('admin.users') ? 'active' : '' }}"
                    data-page="user-management">
                    <i class="fas fa-users"></i>
                    <span>User Management</span>
                </a>
                <a href="{{ route('admin.system-settings') }}"
                    class="menu-item {{ request()->routeIs('admin.system-settings') ? 'active' : '' }}"
                    data-page="system-settings">
                    <i class="fas fa-cog"></i>
                    <span>System Settings</span>
                </a>
                <a href="{{ route('admin.audit-logs') }}"
                    class="menu-item {{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}"
                    data-page="audit-logs">
                    <i class="fas fa-history"></i>
                    <span>Audit Logs</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <h2 id="page-title">Admin Panel</h2>

                <div class="user-info dropdown" style="padding: 6px 16px 6px 6px; cursor: pointer;"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-2" style="width: 40px; height: 40px; font-size: 1rem;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="text-start pe-2">
                            <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem; line-height: 1.2;">{{
                                Auth::user()->name }}</div>
                            <div style="font-size: 0.8rem; color: #64748b; font-weight: 500;">
                                {{ Auth::user()->role ?? 'User' }}
                            </div>
                        </div>
                        <i class="fas fa-chevron-down ms-1" style="color: #94a3b8; font-size: 0.8rem;"></i>
                    </div>
                </div>

                <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                    style="border-radius: 12px; border: 1px solid #e2e8f0; min-width: 220px; padding: 8px 0; margin-top: 10px !important;">
                    <li>
                        <div class="dropdown-item-text px-3 py-2">
                            <strong class="d-block text-dark">{{ Auth::user()->name }}</strong>
                            <small class="text-muted">{{ Auth::user()->email ?? (Auth::user()->role ?? 'Admin')
                                }}</small>
                        </div>
                    </li>
                    <li>
                        <hr class="dropdown-divider" style="border-color: #f1f5f9;">
                    </li>
                    <li>
                        <a class="dropdown-item py-2 px-3 text-secondary d-flex align-items-center bg-transparent"
                            href="{{ route('admin.system-settings') }}"
                            onmouseover="this.style.backgroundColor='#f8fafc'; this.style.color='#3b82f6'"
                            onmouseout="this.style.backgroundColor='transparent'; this.style.color='#6c757d'">
                            <i class="fas fa-cog me-2"></i> Settings
                        </a>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                            @csrf
                            <button type="submit"
                                class="dropdown-item py-2 px-3 text-danger d-flex align-items-center bg-transparent"
                                style="cursor:pointer;" onmouseover="this.style.backgroundColor='#fef2f2';"
                                onmouseout="this.style.backgroundColor='transparent';">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>


            @yield('content')
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('public/assets/main.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if($errors->any())
        let errorMessages = '';
        @foreach($errors->all() as $error)
        errorMessages += '<li>{{ $error }}</li>';
        @endforeach
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: '<ul style="text-align: left; margin-bottom: 0;">' + errorMessages + '</ul>',
            confirmButtonColor: '#3b82f6',
        });
        @endif

        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('
            success ') }}',
            timer: 3000,
            showConfirmButton: false
        });
        @endif

        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('
            error ') }}',
            confirmButtonColor: '#3b82f6',
        });
        @endif

        // Disable mouse-wheel scrolling on numeric inputs globally (TC003)
        document.addEventListener('wheel', function(event) {
            if (document.activeElement.type === 'number') {
                document.activeElement.blur();
            }
        });
    </script>
</body>

</html>