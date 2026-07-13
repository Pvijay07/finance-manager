@extends('Manager.layouts.app')

@section('content')
<div class="manager-panel">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Add New Loan ({{ ucfirst($direction) }})</h4>
        <a href="{{ route('manager.loan-management.index', ['direction' => $direction]) }}" class="btn btn-secondary">Back to List</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('manager.loan-management.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="direction" value="{{ $direction }}">
        
        <!-- Basic Details -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">Basic Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Company *</label>
                        <select name="company_id" class="form-control" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Party (Borrower/Lender) *</label>
                        <select name="party_id" class="form-control" required>
                            <option value="">Select Party</option>
                            @foreach($parties as $party)
                                <option value="{{ $party->id }}">{{ $party->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Loan Name</label>
                        <input type="text" name="loan_name" class="form-control" placeholder="e.g. Vehicle Loan">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Loan Category</label>
                        <input type="text" name="loan_category" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Details -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">Financial Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Principal Amount (₹) *</label>
                        <input type="number" step="0.01" name="principal_amount" class="form-control" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Loan Date *</label>
                        <input type="date" name="loan_date" class="form-control" required>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Disbursement Date</label>
                        <input type="date" name="disbursement_date" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Loan Period (Months)</label>
                        <input type="number" name="loan_period" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Interest Details -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-secondary text-white">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="interest_applicable" name="interest_applicable" value="1">
                    <label class="custom-control-label" for="interest_applicable">Interest Applicable?</label>
                </div>
            </div>
            <div class="card-body" id="interest_section" style="display: none;">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Interest Type</label>
                        <select name="interest_type" class="form-control">
                            <option value="flat">Flat</option>
                            <option value="reducing">Reducing Balance</option>
                            <option value="compound">Compound</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Interest Rate (%)</label>
                        <input type="number" step="0.01" name="interest_rate" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Frequency</label>
                        <select name="interest_frequency" class="form-control">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Interest Start Date</label>
                        <input type="date" name="interest_start_date" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- EMI Details -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-dark text-white">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="enable_emi" name="enable_emi" value="1">
                    <label class="custom-control-label" for="enable_emi">Enable EMI?</label>
                </div>
            </div>
            <div class="card-body" id="emi_section" style="display: none;">
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>EMI Amount (₹)</label>
                        <input type="number" step="0.01" name="emi_amount" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>EMI Count (Installments)</label>
                        <input type="number" name="emi_count" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>EMI Start Date</label>
                        <input type="date" name="emi_start_date" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>EMI Due Day (1-31)</label>
                        <input type="number" min="1" max="31" name="emi_due_day" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax & Processing Details -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">Tax & Charges</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Processing Fee (₹)</label>
                        <input type="number" step="0.01" name="processing_fee" class="form-control" value="0">
                    </div>
                    
                    <div class="col-md-12 mt-3">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="tds_applicable" name="tds_applicable" value="1">
                            <label class="custom-control-label" for="tds_applicable">TDS Applicable?</label>
                        </div>
                    </div>
                    <div class="col-md-4 form-group tds_fields" style="display: none;">
                        <label>TDS Section</label>
                        <input type="text" name="tds_section" class="form-control" placeholder="194A">
                    </div>
                    <div class="col-md-4 form-group tds_fields" style="display: none;">
                        <label>TDS Percent (%)</label>
                        <input type="number" step="0.01" name="tds_percent" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-lg px-5">Save Loan</button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('interest_applicable').addEventListener('change', function() {
        document.getElementById('interest_section').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('enable_emi').addEventListener('change', function() {
        document.getElementById('emi_section').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('tds_applicable').addEventListener('change', function() {
        document.querySelectorAll('.tds_fields').forEach(el => el.style.display = this.checked ? 'block' : 'none');
    });
</script>
@endsection
