<div class="card shadow-sm topbar mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0">Salary Sheet Module</h5>
            <div class="small-help text-muted">Internal salary sheet tracker + CA-ready exports.</div>
        </div>
        <div class="navbtns">
            <a class="btn btn-sm {{ request()->routeIs('manager.salary.dashboard') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('manager.salary.dashboard') }}">Dashboard</a>
            <a class="btn btn-sm {{ request()->routeIs('manager.salary.employees') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('manager.salary.employees') }}">Employees</a>
            <a class="btn btn-sm {{ request()->routeIs('manager.salary.settings') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('manager.salary.settings') }}">Salary Settings</a>
            <a class="btn btn-sm {{ request()->routeIs('manager.salary.sheets') || request()->routeIs('manager.salary.editSheet') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('manager.salary.sheets') }}">Salary Sheets</a>
            <a class="btn btn-sm {{ request()->routeIs('manager.salary.payments') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('manager.salary.payments') }}">Payments</a>
            <a class="btn btn-sm {{ request()->routeIs('manager.salary.reports') ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('manager.salary.reports') }}">Reports</a>
        </div>
    </div>
</div>
