   @extends('Admin.layouts.app')
@section('content')
   <!-- Reports & Analytics Page -->
            <div id="reports" class="page">
                <div class="filter-bar">
                    <div class="filter-group">
                        <div class="filter-label">Date Range</div>
                        <select>
                            <option>Today</option>
                            <option>This Week</option>
                            <option selected>This Month</option>
                            <option>This Quarter</option>
                            <option>This Year</option>
                            <option>Custom Range</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <div class="filter-label">Company</div>
                        <select>
                            <option selected>All Companies</option>
                            <option>Company A</option>
                            <option>Company B</option>
                            <option>Company C</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <div class="filter-label">Report Type</div>
                        <select>
                            <option selected>Financial Summary</option>
                            <option>User Activity</option>
                            <option>System Performance</option>
                        </select>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="chart-container">
                        <div class="chart-header">
                            <div class="chart-title">Financial Overview - All Companies</div>
                        </div>
                        <div class="chart">
                            <canvas id="financialOverviewChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-header">
                            <div class="chart-title">Expense Distribution by Category</div>
                        </div>
                        <div class="chart">
                            <canvas id="expenseDistributionChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <div class="table-title">System Usage Statistics</div>
                        <div class="table-actions">
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i> Export Report
                            </button>
                        </div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Metric</th>
                                <th>This Month</th>
                                <th>Last Month</th>
                                <th>Change</th>
                                <th>YTD Average</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Active Users</td>
                                <td>8</td>
                                <td>7</td>
                                <td style="color: var(--success);">+14.3%</td>
                                <td>6.5</td>
                            </tr>
                            <tr>
                                <td>Expenses Processed</td>
                                <td>142</td>
                                <td>128</td>
                                <td style="color: var(--success);">+10.9%</td>
                                <td>115.2</td>
                            </tr>
                            <tr>
                                <td>Total Transaction Value</td>
                                <td>₹825,420</td>
                                <td>₹745,680</td>
                                <td style="color: var(--success);">+10.7%</td>
                                <td>₹712,350</td>
                            </tr>
                            <tr>
                                <td>System Uptime</td>
                                <td>99.8%</td>
                                <td>99.5%</td>
                                <td style="color: var(--success);">+0.3%</td>
                                <td>99.6%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
@endsection
