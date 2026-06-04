<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Finance Manager</title>
    <!-- Bootstrap CDN -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #10b981;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #0f172a;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --bg-color: #f1f5f9;
            --card-bg: rgba(255, 255, 255, 0.9);
            --sidebar-bg: #1e1b4b;
            /* Deep premium purple/blue */
            --border-radius: 16px;
            --box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --glass-border: 1px solid rgba(255, 255, 255, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            background-image:
                radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 0.05) 0, transparent 50%),
                radial-gradient(at 50% 0%, hsla(225, 39%, 30%, 0.05) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(339, 49%, 30%, 0.05) 0, transparent 50%);
            background-attachment: fixed;
            color: var(--dark);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* Modern KPI Cards */
        .kpi-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 16px;
            padding: 20px;
            transition: var(--transition);
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            background: rgba(255, 255, 255, 0.9);
        }

        .kpi-label {
            font-size: 0.85rem;
            color: var(--gray);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .kpi-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--dark);
        }

        .small-help {
            font-size: 0.75rem;
            color: var(--gray);
            margin-top: 5px;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, #0f172a 100%);
            color: white;
            transition: var(--transition);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1);
            z-index: 100;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .sidebar-header {
            padding: 30px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-header i {
            font-size: 28px;
            color: #818cf8;
            filter: drop-shadow(0 0 8px rgba(129, 140, 248, 0.5));
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(120deg, #e0e7ff, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-menu {
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-item {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            cursor: pointer;
            transition: var(--transition);
            border-radius: 12px;
            text-decoration: none;
            color: #cbd5e1;
            font-weight: 500;
            font-size: 1.05rem;
            border: 1px solid transparent;
        }

        .menu-item:hover {
            background-color: rgba(255, 255, 255, 0.06);
            color: #fff;
            transform: translateX(4px);
        }

        .menu-item.active {
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.2) 0%, rgba(79, 70, 229, 0) 100%);
            color: white;
            border-left: 4px solid #818cf8;
            border-radius: 0 12px 12px 0;
            box-shadow: inset 2px 0 10px rgba(129, 140, 248, 0.1);
        }

        .menu-item i {
            width: 24px;
            font-size: 1.2rem;
            text-align: center;
            transition: var(--transition);
        }

        .menu-item:hover i {
            color: #818cf8;
            transform: scale(1.1);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 30px 40px;
            margin-left: 280px;
            /* Offset for fixed sidebar */
            max-width: calc(100% - 280px);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 30px;
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header h2 {
            color: var(--dark);
            font-weight: 700;
            margin: 0;
            font-size: 1.5rem;
            letter-spacing: -0.5px;
        }

        .user-info {
            background: #f8fafc;
            padding: 8px 16px 8px 8px;
            border-radius: 50px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
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
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 30px;
        }

        .card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: var(--glass-border);
            padding: 24px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            opacity: 0;
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1);
        }

        .card:hover::before {
            opacity: 1;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border: none;
            padding: 0;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: white;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .card-icon.income {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .card-icon.expense {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        .card-icon.profit {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
        }

        .card-icon.upcoming {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .card-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--dark);
            margin: 10px 0;
            letter-spacing: -1px;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed var(--gray-light);
            color: var(--gray);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Table Styles */
        .table-container {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: var(--glass-border);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            padding: 24px 30px;
            background: transparent;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            color: var(--dark) !important;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .table-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            letter-spacing: 0.3px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #6366f1);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid var(--gray-light);
            color: var(--dark);
            backdrop-filter: blur(4px);
        }

        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        th,
        td {
            padding: 16px 24px;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
            vertical-align: middle;
        }

        th {
            background-color: rgba(248, 250, 252, 0.5);
            font-weight: 600;
            color: var(--gray);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: rgba(241, 245, 249, 0.5);
        }

        .status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            backdrop-filter: blur(4px);
        }

        .status.upcoming {
            background-color: rgba(245, 158, 11, 0.15);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status.pending {
            background-color: rgba(59, 130, 246, 0.15);
            color: #2563eb;
            border: 1px solid rgba(59, 130, 246, 0.2);
        }

        .status.overdue {
            background-color: rgba(239, 68, 68, 0.15);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .status.paid {
            background-color: rgba(16, 185, 129, 0.15);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .status.active {
            background-color: rgba(16, 185, 129, 0.15);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* Filter Bar */
        .filter-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            flex-wrap: wrap;
            padding: 24px;
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: var(--glass-border);
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        select,
        input {
            padding: 12px 16px;
            border: 1px solid var(--gray-light);
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--dark);
            transition: var(--transition);
            outline: none;
        }

        select:focus,
        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            background-color: #fff;
        }

        /* Charts */
        .chart-container {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: var(--glass-border);
            padding: 30px;
            margin-bottom: 30px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .chart-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark);
        }

        /* Forms in layout */
        .form-control {
            border-radius: 10px;
            border: 1px solid var(--gray-light);
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                max-width: 100%;
                padding: 20px;
            }
        }

        /* Chrome, Safari, Edge, Opera */
input[type=number]::-webkit-outer-spin-button,
input[type=number]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Firefox */
input[type=number] {
    -moz-appearance: textfield;
}
    </style>
</head>

<body>
    <div class="d-flex">
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-chart-line"></i>
                <h2>Finance Manager</h2>
            </div>
            <div class="sidebar-menu">
                <a href="{{ route('manager.dashboard') }}"
                    class="menu-item {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}"
                    data-page="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('manager.expenses') }}"
                    class="menu-item {{ request()->routeIs('manager.expenses') ? 'active' : '' }}" data-page="expenses">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Expenses</span>
                </a>
              
                {{-- <a href="{{ route('income.upcoming') }}"
                class="menu-item {{ request()->routeIs('income.upcoming') ? 'active' : '' }}"
                data-page="upcoming-payments">
                <i class="fas fa-calendar-day"></i>
                <span>Upcoming Payments</span>
                </a> --}}
                <a href="{{ route('income.index') }}"
                    class="menu-item {{ request()->routeIs('income.index') ? 'active' : '' }}" data-page="income">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Income</span>
                </a>
                <a href="{{ route('manager.gst') }}"
                    class="menu-item {{ request()->routeIs('manager.gst') || request()->routeIs('manager.gst-collected') || request()->routeIs('manager.taxes') ? 'active' : '' }}"
                    data-page="balances">
                    <i class="fas fa-calendar-day"></i>
                    <span>GST & TDS</span>
                </a>
                <a href="{{ route('income.balance') }}"
                    class="menu-item {{ request()->routeIs('income.balance') ? 'active' : '' }}" data-page="balances">
                    <i class="fas fa-balance-scale"></i>
                    <span>Balances & Dues</span>
                </a>
                <a href="{{ route('manager.salary.dashboard') }}"
                    class="menu-item {{ request()->routeIs('manager.salary.*') ? 'active' : '' }}" data-page="salary">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Salary Module</span>
                </a>

                <a href="{{ route('manager.loans.index') }}"
                    class="menu-item {{ request()->routeIs('manager.loans.index') ? 'active' : '' }}"
                    data-page="upcoming-payments">
                    <i class="fas fa-calendar-day"></i>
                    <span>Advances/Loans</span>
                </a>
            </div>
        </div>
        <!-- MAIN CONTENT -->
        <div class="main-content">

            <!-- Top Bar -->
            <div class="header">
                <h2 id="page-title">Manager Panel</h2>

                <div class="user-info dropdown" style="padding: 6px 16px 6px 6px; cursor: pointer;"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="user-avatar me-2"
                            style="width: 40px; height: 40px; font-size: 1rem; border-radius: 50%;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div class="text-start pe-2">
                            <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem; line-height: 1.2;">{{
                                Auth::user()->name }}</div>
                            <div style="font-size: 0.8rem; color: #64748b; font-weight: 500;">
                                {{ Auth::user()->role ?? 'Manager' }}
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
                            <small class="text-muted">{{ Auth::user()->email ?? (Auth::user()->role ?? 'Manager')
                                }}</small>
                        </div>
                    </li>
                    <li>
                        <hr class="dropdown-divider" style="border-color: #f1f5f9;">
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
    <script>
        // Page Navigation
        document.addEventListener('DOMContentLoaded', function() {
            // Menu item click handler
            const menuItems = document.querySelectorAll('.menu-item');
            const pages = document.querySelectorAll('.page');
            const pageTitle = document.getElementById('page-title');

            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');

                    // Update active tab
                    tabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');

                    // Show corresponding content
                    if (tabName === 'standard') {
                        document.getElementById('standard-expenses').style.display = 'block';
                        document.getElementById('non-standard-expenses').style.display = 'none';
                    } else if (tabName === 'non-standard') {
                        document.getElementById('standard-expenses').style.display = 'none';
                        document.getElementById('non-standard-expenses').style.display = 'block';
                    }

                    // For upcoming payments tabs
                    if (tabName === 'all' || tabName === 'debits' || tabName === 'credits' ||
                        tabName === 'overdue') {
                        // In a real app, you would filter the table here
                        console.log(`Switched to ${tabName} tab`);
                    }
                });
            });

            // Modal functionality
            const modal = document.getElementById('expense-modal');
            const addExpenseBtn = document.getElementById('add-expense-btn');
            const closeModalBtns = document.querySelectorAll('.close-modal');

            if (addExpenseBtn) {
                addExpenseBtn.addEventListener('click', function() {
                    modal.style.display = 'flex';
                });
            }

            closeModalBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    modal.style.display = 'none';
                });
            });

            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });

            // Mark as Paid button functionality
            const markPaidButtons = document.querySelectorAll('.mark-paid-btn');
            markPaidButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const statusCell = row.querySelector('.status');
                    statusCell.textContent = 'Paid';
                    statusCell.className = 'status paid';
                    this.innerHTML = '<i class="fas fa-check"></i> Paid';
                    this.classList.remove('btn-success');
                    this.classList.add('btn-outline');
                    this.disabled = true;
                });
            });

            // Initialize Charts
            const profitLossCanvas = document.getElementById('profitLossChart');
            if (profitLossCanvas) {
                const profitLossCtx = profitLossCanvas.getContext('2d');
                const profitLossChart = new Chart(profitLossCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Company A', 'Company B', 'Company C'],
                        datasets: [{
                                label: 'Income',
                                data: [125000, 95000, 75000],
                                backgroundColor: '#27ae60'
                            },
                            {
                                label: 'Expenses',
                                data: [85000, 72000, 68000],
                                backgroundColor: '#e74c3c'
                            },
                        {
                            label: 'Net Profit',
                            data: [40000, 23000, 7000],
                            backgroundColor: '#3498db'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            }

            const detailedProfitLossCanvas = document.getElementById('detailedProfitLossChart');
            if (detailedProfitLossCanvas) {
                const detailedProfitLossCtx = detailedProfitLossCanvas.getContext('2d');
                const detailedProfitLossChart = new Chart(detailedProfitLossCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                                label: 'Income',
                                data: [220000, 240000, 245000, 260000, 255000, 270000],
                                borderColor: '#27ae60',
                                backgroundColor: 'rgba(39, 174, 96, 0.1)',
                                fill: true
                            },
                            {
                                label: 'Expenses',
                                data: [180000, 190000, 187000, 195000, 200000, 205000],
                                borderColor: '#e74c3c',
                                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                                fill: true
                            },
                        {
                            label: 'Net Profit',
                            data: [40000, 50000, 58000, 65000, 55000, 65000],
                            borderColor: '#3498db',
                            backgroundColor: 'rgba(52, 152, 219, 0.1)',
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
document.querySelectorAll('input[type="number"]').forEach(input => {

    // Prevent mouse wheel
    input.addEventListener('wheel', function(e) {
        e.preventDefault();
    }, { passive: false });

    // Prevent arrow up/down keys
    input.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            e.preventDefault();
        }
    });

    // Clear 0.00 on focus
    input.addEventListener('focus', function() {
        if (this.value === '0' || this.value === '0.00') {
            this.value = '';
        }
    });

    // Restore 0.00 on blur if empty
    input.addEventListener('blur', function() {
        if (this.value === '') {
            this.value = '0.00';
        }
    });

});
    </script>
</body>

</html>