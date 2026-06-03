  @extends('Admin.layouts.app')
@section('content')
  <!-- Audit Logs Page -->
            <div id="audit-logs" class="page">
                <div class="filter-bar">
                    <div class="filter-group">
                        <div class="filter-label">Date Range</div>
                        <select>
                            <option>Today</option>
                            <option selected>Last 7 Days</option>
                            <option>Last 30 Days</option>
                            <option>Custom Range</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <div class="filter-label">User</div>
                        <select>
                            <option selected>All Users</option>
                            <option>Super Admin</option>
                            <option>John Manager</option>
                            <option>Raj Manager</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <div class="filter-label">Action Type</div>
                            <select>
                            <option selected>All Actions</option>
                            <option>Login</option>
                            <option>Create</option>
                            <option>Update</option>
                            <option>Delete</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <div class="filter-label">Resource</div>
                        <select>
                            <option selected>All Resources</option>
                            <option>User</option>
                            <option>Company</option>
                            <option>Expense</option>
                            <option>Settings</option>
                        </select>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <div class="table-title">Audit Logs</div>
                        <div class="table-actions">
                            <button class="btn btn-outline">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i> Export Logs
                            </button>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Resource</th>
                                <th>Details</th>
                                <th>IP Address</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Today, 10:25 AM</td>
                                <td>John Manager</td>
                                <td>Marked as Paid</td>
                                <td>Expense: Office Rent</td>
                                <td>Company A - ₹25,000</td>
                                <td>192.168.1.105</td>
                                <td><span class="status active">Success</span></td>
                            </tr>
                            <tr>
                                <td>Today, 09:45 AM</td>
                                <td>Super Admin</td>
                                <td>Created User</td>
                                <td>User: Priya Sharma</td>
                                <td>Role: Manager, Company: Company A</td>
                                <td>192.168.1.100</td>
                                <td><span class="status active">Success</span></td>
                            </tr>
                            <tr>
                                <td>Today, 09:30 AM</td>
                                <td>Raj Manager</td>
                                <td>Added Expense</td>
                                <td>Non-standard: Server Maintenance</td>
                                <td>Company B - ₹8,500</td>
                                <td>192.168.1.102</td>
                                <td><span class="status active">Success</span></td>
                            </tr>
                            <tr>
                                <td>Yesterday, 05:15 PM</td>
                                <td>Super Admin</td>
                                <td>Updated Settings</td>
                                <td>System: Reminder Days</td>
                                <td>Changed from 5 to 7 days</td>
                                <td>192.168.1.100</td>
                                <td><span class="status active">Success</span></td>
                            </tr>
                            <tr>
                                <td>Yesterday, 04:30 PM</td>
                                <td>John Manager</td>
                                <td>Failed Login</td>
                                <td>Authentication</td>
                                <td>Invalid password attempt</td>
                                <td>192.168.1.105</td>
                                <td><span class="status inactive">Failed</span></td>
                            </tr>
                            <tr>
                                <td>Yesterday, 03:45 PM</td>
                                <td>Super Admin</td>
                                <td>Exported Report</td>
                                <td>Report: Financial Summary</td>
                                <td>Date range: This Month, Format: Excel</td>
                                <td>192.168.1.100</td>
                                <td><span class="status active">Success</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Audit Log Settings</div>
                    </div>
                    <div style="padding: 20px;">
                        <div class="form-group">
                            <label class="form-label">Log Retention Period</label>
                            <select class="form-control">
                                <option>30 days</option>
                                <option>90 days</option>
                                <option selected>1 year</option>
                                <option>2 years</option>
                                <option>Indefinite</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Log Detail Level</label>
                            <select class="form-control">
                                <option>Minimal</option>
                                <option selected>Standard</option>
                                <option>Detailed</option>
                                <option>Verbose</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" checked> Log user logins
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" checked> Log data modifications
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox"> Log data reads
                            </label>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                </div>
            </div>
@endsection
