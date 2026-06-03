@extends('Admin.layouts.app')

@section('content')
    <meta name="companies-store-url" content="{{ route('admin.companies.store') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div id="company-management" class="page">
        <div class="table-container">
            <div class="table-header">
                <div class="table-title">Company Management</div>
                <div class="table-actions">
                    <button class="btn btn-outline">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button class="btn btn-primary" id="add-company-btn">
                        <i class="fas fa-plus"></i> Add Company
                    </button>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Company Code</th>
                        <th>Company Name</th>
                        <th>Manager</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Expense Types</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($companies as $company)
                        <tr>
                            <td>{{ $company->code }}</td>
                            <td>{{ $company->name }}</td>
                            <td>{{ $company->manager ? $company->manager->name : 'Unassigned' }}</td>
                            <td><span
                                    class="status {{ $company->status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($company->status) }}</span>
                            </td>
                            <td>{{ date('d-M-y', strtotime($company->created_at)) }}</td>
                            <td><span class="badge badge-primary">0  Types</span></td>
                            <td>
                                <button class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem;">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem;">
                                    <i class="fas fa-cog"></i> Settings
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Company Settings</div>
            </div>
            <div style="padding: 20px;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Selected Company</label>
                        <select class="form-control">
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Assigned Manager</label>
                        <select class="form-control">
                            <option>John Manager</option>
                            <option>Raj Manager</option>
                            <option>Priya Sharma</option>
                            <option>Unassigned</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Financial Year Start</label>
                    <input type="date" class="form-control" value="2024-04-01">
                </div>
                <div class="form-group">
                    <label class="form-label">Default Currency</label>
                    <select class="form-control">
                        <option>Indian Rupee (₹)</option>
                        <option>US Dollar ($)</option>
                        <option>Euro (€)</option>
                    </select>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary">Save Settings</button>
                </div>
            </div>
        </div>
    </div>
@endsection
