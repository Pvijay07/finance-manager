
           @extends('Admin.layouts.app')
@section('content')
           <!-- System Settings Page -->
            <div id="system-settings" class="page">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">General Settings</div>
                    </div>
                    <div style="padding: 20px;">
                        <div class="form-group">
                            <label class="form-label">Application Name</label>
                            <input type="text" class="form-control" value="Finance Manager">
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
                            <label class="form-label">Date Format</label>
                            <select class="form-control">
                                <option>DD/MM/YYYY</option>
                                <option selected>MM/DD/YYYY</option>
                                <option>YYYY-MM-DD</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Session Timeout (minutes)</label>
                            <input type="number" class="form-control" value="30" min="5" max="240">
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Email & Notification Settings</div>
                    </div>
                    <div style="padding: 20px;">
                        <div class="form-group">
                            <label class="form-label">SMTP Server</label>
                            <input type="text" class="form-control" value="smtp.company.com">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" value="587">
                            </div>
                            <div class="form-group">
                                <label class="form-label">SSL/TLS</label>
                                <select class="form-control">
                                    <option>None</option>
                                    <option selected>STARTTLS</option>
                                    <option>SSL/TLS</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Default Reminder Days</label>
                            <input type="number" class="form-control" value="7" min="1" max="30">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <input type="checkbox" checked> Enable email notifications
                            </label>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary">Save Settings</button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Backup & Maintenance</div>
                    </div>
                    <div style="padding: 20px;">
                        <div class="form-group">
                            <label class="form-label">Automatic Backup</label>
                            <select class="form-control">
                                <option>Disabled</option>
                                <option>Daily</option>
                                <option selected>Weekly</option>
                                <option>Monthly</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Backup</label>
                            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px;">
                                <div><strong>Date:</strong> 15 March 2024, 02:00 AM</div>
                                <div><strong>Status:</strong> <span class="status active">Success</span></div>
                                <div><strong>Size:</strong> 45.2 MB</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i> Download Latest Backup
                            </button>
                            <button class="btn btn-outline">
                                <i class="fas fa-play"></i> Run Backup Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
@endsection
