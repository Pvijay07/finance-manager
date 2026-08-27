<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Manager Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>window.APP_URL = "{{ url('/') }}";</script>

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Manrope:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">

    <!-- Tailwind CDN with Plugins -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "primary": "#4f46e5",
                        "on-primary": "#ffffff",
                        "primary-container": "#e0e7ff",
                        "on-primary-container": "#111827",
                        "secondary": "#4f46e5",
                        "on-secondary": "#ffffff",
                        "secondary-container": "#f5f3ff",
                        "on-secondary-container": "#4f46e5",
                        "tertiary": "#10b981",
                        "on-tertiary": "#ffffff",
                        "tertiary-container": "#ecfdf5",
                        "on-tertiary-container": "#047857",
                        "error": "#ef4444",
                        "on-error": "#ffffff",
                        "error-container": "#fee2e2",
                        "on-error-container": "#991b1b",
                        "outline": "#d1d5db",
                        "outline-variant": "#e5e7eb",
                        "background": "#f9fafb",
                        "on-background": "#111827",
                        "surface": "#ffffff",
                        "on-surface": "#111827",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f9fafb",
                        "surface-container": "#f3f4f6",
                        "surface-container-high": "#e5e7eb",
                        "surface-container-highest": "#d1d5db",
                        "on-surface-variant": "#4b5563",
                        "secondary-fixed": "#e0e7ff",
                        "tertiary-fixed": "#d1fae5",
                        "on-tertiary-fixed-variant": "#065f46",
                        "secondary-fixed-dim": "#c7d2fe"
                    },
                    "spacing": {
                        "xs": "4px",
                        "sm": "8px",
                        "md": "16px",
                        "lg": "24px",
                        "xl": "32px",
                        "xxl": "48px",
                        "margin-mobile": "16px",
                        "margin-tablet": "24px",
                        "margin-desktop": "32px"
                    },
                    "fontSize": {
                        "display-lg": ["57px", "64px"],
                        "display-md": ["45px", "52px"],
                        "display-sm": ["36px", "44px"],
                        "headline-lg": ["32px", "40px"],
                        "headline-md": ["28px", "36px"],
                        "headline-sm": ["24px", "32px"],
                        "title-lg": ["22px", "28px"],
                        "title-md": ["16px", "24px"],
                        "title-sm": ["14px", "20px"],
                        "body-lg": ["16px", "24px"],
                        "body-md": ["14px", "20px"],
                        "body-sm": ["12px", "16px"],
                        "label-lg": ["14px", "20px"],
                        "label-md": ["12px", "16px"],
                        "label-sm": ["11px", "16px"]
                    },
                    "borderRadius": {
                        "none": "0",
                        "xs": "4px",
                        "sm": "8px",
                        "md": "12px",
                        "lg": "16px",
                        "xl": "24px",
                        "full": "9999px"
                    }
                }
            }
        }
    </script>
    <style>
        /* Responsive Sidebar overrides for the mockup */
        @media (max-width: 1024px) {
            aside {
                transform: translateX(-100%) !important;
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }
            aside.show-mobile-sidebar {
                transform: translateX(0) !important;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.15) !important;
            }
            main {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            header {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }
        }

        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined', sans-serif !important;
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            line-height: 1;
            text-transform: none;
            letter-spacing: normal;
            word-wrap: normal;
            white-space: nowrap;
            direction: ltr;
        }

        /* Fix FontAwesome icons being overridden by generic !important rules */
        .fas, .fa-solid, .fa, .far, .fab {
            font-family: "Font Awesome 6 Free" !important;
        }

        .card-shadow {
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.05);
        }

        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #dce9ff; border-radius: 10px; }

        /* Force action buttons and tabs to be visible */
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
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div id="mobile-sidebar-overlay" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden" onclick="toggleMobileSidebar()"></div>

    <!-- SideNavBar Shell -->
    <aside class="flex flex-col h-screen p-lg fixed left-0 top-0 z-50 overflow-y-auto w-[280px] custom-scrollbar" style="background-color: #1e293b; border-right: none;">
        <div class="mb-xl px-2">
            <h1 class="font-headline-sm text-headline-sm font-bold tracking-wide text-white" style="font-size: 1.25rem;">FinCorp</h1>
            <p class="text-xs font-semibold tracking-widest uppercase mt-1" style="color: #94a3b8;">MANAGER PANEL</p>
        </div>
       <nav class="flex-1 space-y-2">
    <a class="flex items-center gap-md px-md py-3 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('manager.dashboard') ? 'text-white font-medium shadow-sm' : 'hover:text-white' }}"
       style="{{ request()->routeIs('manager.dashboard') ? 'background-color: #334155;' : 'color: #94a3b8;' }}"
       href="{{ route('manager.dashboard') }}">
        <span class="material-symbols-outlined text-[24px]">dashboard</span>
        <span class="text-base font-medium">Dashboard</span>
    </a>

    <a class="flex items-center gap-md px-md py-3 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('manager.expenses') ? 'text-white font-medium shadow-sm' : 'hover:text-white' }}"
       style="{{ request()->routeIs('manager.expenses') ? 'background-color: #334155;' : 'color: #94a3b8;' }}"
       href="{{ route('manager.expenses') }}">
        <span class="material-symbols-outlined text-[24px]">payments</span>
        <span class="text-base font-medium">Expenses</span>
    </a>

    <a class="flex items-center gap-md px-md py-3 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('income.index') ? 'text-white font-medium shadow-sm' : 'hover:text-white' }}"
       style="{{ request()->routeIs('income.index') ? 'background-color: #334155;' : 'color: #94a3b8;' }}"
       href="{{ route('income.index') }}">
        <span class="material-symbols-outlined text-[24px]">account_balance_wallet</span>
        <span class="text-base font-medium">Income</span>
    </a>

    <a class="flex items-center gap-md px-md py-3 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('manager.gst') || request()->routeIs('manager.gst-collected') || request()->routeIs('manager.taxes') ? 'text-white font-medium shadow-sm' : 'hover:text-white' }}"
       style="{{ request()->routeIs('manager.gst') || request()->routeIs('manager.gst-collected') || request()->routeIs('manager.taxes') ? 'background-color: #334155;' : 'color: #94a3b8;' }}"
       href="{{ route('manager.gst') }}">
        <span class="material-symbols-outlined text-[24px]">receipt_long</span>
        <span class="text-base font-medium">GST & TDS</span>
    </a>

    <a class="flex items-center gap-md px-md py-3 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('income.balance') ? 'text-white font-medium shadow-sm' : 'hover:text-white' }}"
       style="{{ request()->routeIs('income.balance') ? 'background-color: #334155;' : 'color: #94a3b8;' }}"
       href="{{ route('income.balance') }}">
        <span class="material-symbols-outlined text-[24px]">account_balance</span>
        <span class="text-base font-medium">Balances & Dues</span>
    </a>

    <a class="flex items-center gap-md px-md py-3 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('manager.salary.dashboard') || request()->routeIs('manager.salary.*') ? 'text-white font-medium shadow-sm' : 'hover:text-white' }}"
       style="{{ request()->routeIs('manager.salary.dashboard') || request()->routeIs('manager.salary.*') ? 'background-color: #334155;' : 'color: #94a3b8;' }}"
       href="{{ route('manager.salary.dashboard') }}">
        <span class="material-symbols-outlined text-[24px]">analytics</span>
        <span class="text-base font-medium">Salary Module</span>
    </a>

    <a class="flex items-center gap-md px-md py-3 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('manager.loans.index') ? 'text-white font-medium shadow-sm' : 'hover:text-white' }}"
       style="{{ request()->routeIs('manager.loans.index') ? 'background-color: #334155;' : 'color: #94a3b8;' }}"
       href="{{ route('manager.loans.index') }}">
        <span class="material-symbols-outlined text-[24px]">handshake</span>
        <span class="text-base font-medium">Advances</span>
    </a>

    <a class="flex items-center gap-md px-md py-3 rounded-xl transition-all duration-200 ease-in-out {{ request()->routeIs('manager.loan-management.*') ? 'text-white font-medium shadow-sm' : 'hover:text-white' }}"
       style="{{ request()->routeIs('manager.loan-management.*') ? 'background-color: #334155;' : 'color: #94a3b8;' }}"
       href="{{ route('manager.loan-management.index') }}">
        <span class="material-symbols-outlined text-[24px]">real_estate_agent</span>
        <span class="text-base font-medium">Loan Management</span>
    </a>
</nav>
        
   

        <div class="mt-6 pt-6 space-y-2 px-2" style="border-top: 1px solid #334155;">
            <a class="flex items-center gap-md px-md py-3 rounded-xl hover:text-white transition-all cursor-pointer text-decoration-none" style="color: #94a3b8;" onmouseover="this.style.backgroundColor='#334155'; this.style.color='white'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#94a3b8'" onclick="openChangePasswordModal()">
                <span class="material-symbols-outlined text-[20px]">lock_reset</span>
                <span class="font-label-md text-label-md">Change Password</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="m-0 p-0" id="logout-form">
                @csrf
                <a class="flex items-center gap-md px-md py-3 rounded-xl hover:text-white transition-all cursor-pointer text-decoration-none" style="color: #94a3b8;" onmouseover="this.style.backgroundColor='#334155'; this.style.color='white'" onmouseout="this.style.backgroundColor='transparent'; this.style.color='#94a3b8'" onclick="document.getElementById('logout-form').submit();">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    <span class="font-label-md text-label-md">Sign Out</span>
                </a>
            </form>
        </div>
    </aside>

    <!-- Main Workspace -->
    <main class="lg:ml-[280px] min-h-screen flex flex-col">
        <!-- TopNavBar Shell -->
        <header class="flex justify-between items-center w-full px-margin-desktop h-16 sticky top-0 z-40 bg-surface shadow-sm border-b border-outline-variant" style="background-color: white !important;">
            <div class="flex items-center gap-md lg:gap-xl">
                <!-- Mobile Menu Toggle -->
                <button class="lg:hidden material-symbols-outlined text-on-surface-variant cursor-pointer p-sm rounded-full hover:bg-surface-container-low transition-colors border-0 bg-transparent" onclick="toggleMobileSidebar()">
                    menu
                </button>
                <h2 class="font-headline-md text-headline-md text-primary font-bold">Manager Panel</h2>
                
            </div>
            <div class="flex items-center gap-lg">
                <button class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:bg-surface-container-low p-sm rounded-full transition-colors border-0 bg-transparent">notifications</button>
                <button class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:bg-surface-container-low p-sm rounded-full transition-colors border-0 bg-transparent">settings</button>
                <div class="dropdown">
                    <div class="flex items-center gap-sm pl-md border-l border-outline-variant cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <div class="text-right hidden sm:block">
                            <p class="font-label-md text-label-md font-bold mb-0 text-slate-800">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-on-surface-variant uppercase tracking-wider mb-0">{{ Auth::user()->role ?? 'Manager' }}</p>
                        </div>
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4f46e5, #818cf8); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1rem; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.3);">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <i class="fas fa-chevron-down text-slate-400 text-xs ml-1"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2 mt-2" style="border-radius: 14px; min-width: 220px; z-index: 1050;">
                        <li class="px-3 py-2 border-bottom mb-1">
                            <p class="font-bold text-sm text-slate-800 mb-0">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-500 mb-0">{{ Auth::user()->email }}</p>
                            <span class="badge bg-primary-subtle text-primary text-[10px] mt-1 text-uppercase">{{ Auth::user()->role ?? 'Manager' }}</span>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-2 text-slate-700 hover:bg-slate-50" href="javascript:void(0)" onclick="openChangePasswordModal()">
                                <i class="fas fa-key text-primary" style="width: 18px;"></i>
                                <span class="font-medium text-sm">Change Password</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                                @csrf
                                <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 rounded-2 text-danger hover:bg-red-50 w-100 border-0 bg-transparent">
                                    <i class="fas fa-sign-out-alt text-danger" style="width: 18px;"></i>
                                    <span class="font-medium text-sm">Sign Out</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content Canvas -->
        <div class="p-margin-desktop space-y-xl">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="mt-auto p-margin-desktop py-lg border-t border-outline-variant text-center">
            <p class="font-label-md text-label-md text-on-surface-variant mb-0">© 2026 FinCorp Global Solutions. All rights reserved. Secure 256-bit SSL encrypted dashboard.</p>
        </footer>
    </main>

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.querySelector('aside');
            const overlay = document.getElementById('mobile-sidebar-overlay');
            if (sidebar && overlay) {
                sidebar.classList.toggle('show-mobile-sidebar');
                overlay.classList.toggle('hidden');
            }
        }
    </script>
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
    @include('partials.change_password_modal')
</body>

</html>