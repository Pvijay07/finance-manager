@extends('Manager.layouts.app')
@section('content')
    <section class="pge">
        <div class="container-fluid">

            <div class="card shadow-sm mb-3">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="mb-0">TDS Collected (Output TDS)</h5>
                        <div class="small-help">From taxable sales invoices. Attach invoice PDFs for CA export.</div>
                    </div>
                    <div class="topnav">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst') }}">Dashboard</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.gst-collected') }}">GST
                            Collected</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.taxes') }}">GST Payable</a>
                        <a class="btn btn-sm btn-primary" href="{{ route('manager.tds') }}">TDS on Income</a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('manager.tdsExpense') }}">TDS on
                            Expense</a>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Tax Period</div>
                            <div class="value">{{ date('F Y', strtotime($period . '-01')) }}</div>
                            <div class="small-help">Currently viewing</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Total Output TDS</div>
                            <div class="value">₹ {{ number_format($totalOutputTDS, 2) }}</div>
                            <div class="small-help">From invoices</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Taxable Amount</div>
                            <div class="value">₹ {{ number_format($totalTaxableAmount, 2) }}</div>
                            <div class="small-help">Base amount before TDS</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card kpi shadow-sm">
                        <div class="card-body">
                            <div class="label">Attachments</div>
                            <div class="value"></div>
                            <div class="small-help">PDFs for CA</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h6 class="mb-1">Filter Sales Invoices</h6>
                            <div class="small-help">View and filter taxable sales invoices.</div>
                        </div>
                        <!-- <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#attachModal">Attach Invoice PDF</button>
                            </div> -->
                    </div>

                    <form class="row g-2 align-items-end" id="invoiceFilterForm" method="GET"
                        action="{{ route('manager.tds') }}">
                        @csrf
                        <div class="col-md-2">
                            <label class="form-label small">Company</label>
                            <select class="form-select form-select-sm" name="company_id" id="invoice_company_filter">
                                <option value="all">All</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Tax Period</label>
                            <select class="form-select form-select-sm" name="period" id="invoice_period_filter">
                                @foreach ($months as $month)
                                    <option value="{{ $month['value'] }}" {{ $period == $month['value'] ? 'selected' : '' }}>
                                        {{ $month['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Status</label>
                            <select class="form-select form-select-sm" name="status" id="invoice_status_filter">
                                <option value="all">All Status</option>
                                <option value="paid" {{ ($selectedStatus ?? 'all') == 'paid' ? 'selected' : '' }}>Paid
                                </option>
                                <option value="pending" {{ ($selectedStatus ?? 'all') == 'pending' ? 'selected' : '' }}>
                                    Pending</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Search</label>
                            <input type="text" class="form-control form-control-sm" name="search" id="invoice_search_filter" value="{{ request('search') }}" placeholder="Invoice No / Client">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Sales Invoices (Output TDS)</span>
                    <div class="d-flex align-items-center gap-2">
                        <select class="form-select form-select-sm border-0 bg-light" style="width: auto;"
                            id="perPageSelector" onchange="updatePerPage()">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 per page</option>
                            <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20
                                per page</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                        </select>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportInvoiceData('excel')">
                            Export Excel
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportInvoiceData('pdf')">
                            Export PDF
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportInvoiceData('zip')">
                            Export Attachments
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th width="15%"><i class="bi bi-hash me-1"></i>Invoice No</th>
                                    <th>Company</th>
                                    <th>Client</th>
                                    <th>Invoice Date</th>
                                    <th class="text-end">Taxable Amount (₹)</th>
                                    <th class="text-end">TDS Amount (₹)</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($salesInvoices as $tax)
                                    @php
                                        $invoice = $tax->taxable;
                                        $clientDetails = $invoice && $invoice->client_details ? json_decode($invoice->client_details, true) : [];
                                        $companyName = $invoice && $invoice->company ? $invoice->company->name : 'N/A';
                                        $clientName = $clientDetails['name'] ?? ($invoice->client->name ?? ($invoice->customer_name ?? ($invoice->client_name ?? ($invoice->customer ?? 'N/A'))));
                                        $invoiceDate = $invoice && ($invoice->issue_date ?? ($invoice->date ?? ($invoice->invoice_date ?? $invoice->created_at))) ? date('d M Y', strtotime($invoice->issue_date ?? ($invoice->date ?? ($invoice->invoice_date ?? $invoice->created_at)))) : 'N/A';
                                        $taxableAmount = $invoice->subtotal ?? ($invoice->amount ?? ($invoice->total ?? ($invoice->total_amount ?? 0)));
                                        $tdsAmount = $tax->tax_amount ?? 0;
                                        $status = $tax->payment_status ?? ($invoice->status ?? 'Pending');

                                        $statusClass = match (strtolower($status)) {
                                            'paid', 'completed', 'received' => 'text-success',
                                            'pending' => 'text-warning',
                                            'not_received', 'overdue' => 'text-danger',
                                            default => 'text-secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('manager.income.view', $invoice->id ?? $tax->id) }}" class="fw-bold text-primary text-decoration-none">
                                                {{ $invoice->invoice_number ?? ('#INC-' . ($invoice->id ?? $tax->id)) }}
                                            </a>
                                        </td>
                                        <td>{{ $companyName }}</td>
                                        <td>{{ $clientName }}</td>
                                        <td>{{ $invoiceDate }}</td>
                                        <td class="text-end">{{ number_format($tax->taxable_amount, 2) }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($tdsAmount, 2) }}</td>
                                        <td>
                                            <span class="fw-medium {{ $statusClass }}">
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </span>
                                        </td>

                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                @if ($tax && $tax->payment_status !== 'received')
                                                    <button class="btn btn-sm btn-outline-success"
                                                        onclick="openTdsAttachmentModal({{ $tax->id }}, '{{ $tax->tax_type }} TDS')">
                                                        Mark Paid
                                                    </button>
                                                @else
                                                    @if(!empty($tax->tds_proof_path))
                                                        <button class="btn btn-outline-secondary btn-sm"
                                                            onclick="window.location.href='{{ route('manager.tds.download-tds-proof', $tax->id) }}'">
                                                            Download
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No TDS income records found for this
                                            period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($salesInvoices->count() > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-semibold">Total:</td>
                                    <td class="text-end fw-semibold">
                                        ₹ {{ number_format($totalTaxableAmount, 2) }}
                                    </td>
                                    <td class="text-end fw-semibold">
                                        ₹ {{ number_format($totalOutputTDS, 2) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                    <!-- Pagination -->
                    @if ($salesInvoices->hasPages() || $salesInvoices->total() > 0)
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div class="text-muted small">
                                @if ($salesInvoices->total() > 0)
                                    Showing {{ $salesInvoices->firstItem() }} to {{ $salesInvoices->lastItem() }} of
                                    {{ $salesInvoices->total() }} entries
                                @else
                                    No entries found
                                @endif
                            </div>
                            <div class="laravel-pagination">
                                {{ $salesInvoices->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Attach Invoice Modal -->
            <div class="modal fade" id="attachModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title">Attach Invoice PDF</h5>
                                <div class="small-help">Upload invoice PDF for CA export.</div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form class="row g-3" id="attachInvoiceForm" method="POST"
                                action="{{ route('manager.tds.attach') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label small">Select Invoice</label>
                                    <select class="form-select form-select-sm" name="invoice_id" required>
                                        <option value="">Select Invoice</option>
                                        @foreach ($salesInvoices as $tax)
                                            @if ($tax->taxable)
                                                <option value="{{ $tax->taxable->id }}">
                                                    {{ $tax->taxable->invoice_number ?? 'Invoice #' . $tax->id }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Attach PDF</label>
                                    <input type="file" class="form-control form-control-sm" name="pdf_file" accept=".pdf"
                                        required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Notes</label>
                                    <textarea class="form-control form-control-sm" name="notes" rows="2"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                            <button class="btn btn-sm btn-primary" type="submit" form="attachInvoiceForm">Save</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Attachment Modal for Individual Invoice -->
            <div class="modal fade" id="attachmentModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="attachmentModalTitle">Attach Document</h5>
                                <div class="small-help">Invoice: <span id="attachmentInvoiceNumber"></span></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Upload Form -->
                            <form id="uploadAttachmentForm" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="tax_id" id="attachment_invoice_id">

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label small">Document Type *</label>
                                        TDS Certificate

                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Upload File *</label>
                                        <input type="file" class="form-control form-control-sm" name="tds_proof"
                                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                        <small class="text-muted">Max: 5MB. Allowed: PDF, Images, DOC</small>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small">Description</label>
                                        <textarea class="form-control form-control-sm" name="description" rows="2"
                                            placeholder="Add notes about this attachment..."></textarea>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-sm btn-primary" id="uploadBtn">
                                        <i class="fas fa-upload"></i> Upload Attachment
                                    </button>
                                </div>
                            </form>


                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function exportInvoiceData(type) {
            const companyElement = document.getElementById('invoice_company_filter');
            const periodElement = document.getElementById('invoice_period_filter');
            const statusElement = document.getElementById('invoice_status_filter');

            const company = companyElement ? companyElement.value : 'all';
            const period = periodElement ? periodElement.value : '';
            const status = statusElement ? statusElement.value : 'received';

            window.location.href =
                `{{ url('manager/tds/export') }}/${type}?company=${company}&period=${period}&status=${status}`;
        }

        function downloadInvoice(id) {
            window.location.href = `{{ url('manager/tds/download-invoice') }}/${id}`;
        }

        function viewAttachments(id) {
            window.location.href = `{{ url('manager/tds/attachments') }}/${id}`;
        }

        function attachToInvoice(id) {
            // Open modal and set the invoice ID
            $('#attachModal select[name="invoice_id"]').val(id);
            $('#attachModal').modal('show');
        }

        /* function syncFromInvoices() {
            const period = document.getElementById('invoice_period_filter').value;

            fetch('{{ route('manager.tds.sync') }}', {
        method: 'POST',
            headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                        },
        body: JSON.stringify({
            period: period
        })
                    })
                .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Invoices synced successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while syncing invoices.');
            });
        }*/
    </script>
    <script>
        // Open attachment modal and load existing attachments
        function openTdsAttachmentModal(taxId, tax_type = '') {


            // Set invoice ID in form
            document.getElementById('attachment_invoice_id').value = taxId;

            // Reset form
            document.getElementById('uploadAttachmentForm').reset();

            // Set form action
            document.getElementById('uploadAttachmentForm').action = `{{ route('manager.tds.attach-document') }}`;

            // Load existing attachments
            // loadAttachments(invoiceId);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('attachmentModal'));
            modal.show();
        }

        // Load existing attachments
        function loadAttachments(invoiceId) {
            const attachmentsList = document.getElementById('attachmentsList');
            attachmentsList.innerHTML =
                '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading attachments...</div>';

            fetch(`{{ url('https://xhtmlreviews.in/beta-finance/manager/tds/attachments') }}/${invoiceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.attachments && data.attachments.length > 0) {
                        let html = '';
                        data.attachments.forEach(attachment => {
                            const fileSize = (attachment.file_size / 1024).toFixed(1); // Convert to KB
                            const uploadDate = new Date(attachment.created_at).toLocaleDateString('en-IN', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric'
                            });

                            html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-medium">
                                    <i class="fas fa-file-${getFileIcon(attachment.file_type)} text-primary me-2"></i>
                                    ${attachment.document_type || 'Document'}
                                </div>
                                <small class="text-muted">${attachment.description || 'No description'}</small>
                                <div class="small text-muted mt-1">
                                    <i class="fas fa-calendar"></i> ${uploadDate} 
                                    • <i class="fas fa-hdd"></i> ${fileSize} KB
                                </div>
                            </div>
                            <div class="btn-group">
                                <a href="${attachment.file_url}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="${attachment.file_url}" 
                                   class="btn btn-sm btn-outline-secondary" 
                                   download>
                                    <i class="fas fa-download"></i>
                                </a>
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="deleteAttachment(${attachment.id}, ${invoiceId})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>`;
                        });
                        attachmentsList.innerHTML = html;
                    } else {
                        attachmentsList.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No attachments found</p>
                    </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error loading attachments:', error);
                    attachmentsList.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i>
                    <p class="text-danger">Error loading attachments</p>
                </div>`;
                });
        }

        // Get file icon based on file type
        function getFileIcon(fileType) {
            if (fileType === 'pdf') return 'pdf';
            if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType)) return 'image';
            if (['doc', 'docx'].includes(fileType)) return 'word';
            return 'alt';
        }

        // Delete attachment
        function deleteAttachment(attachmentId, invoiceId) {
            if (!confirm('Are you sure you want to delete this attachment?')) return;

            fetch(`{{ url('manager/tds/delete-attachment') }}/${attachmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Attachment deleted successfully');
                        // loadAttachments(invoiceId); // Reload attachments
                    } else {
                        alert('Error: ' + (data.message || 'Failed to delete attachment'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting attachment');
                });
        }

        // Handle attachment form submission
        document.getElementById('uploadAttachmentForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);
            const uploadBtn = document.getElementById('uploadBtn');
            const originalBtnText = uploadBtn.innerHTML;

            // Show loading state
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            uploadBtn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Attachment uploaded successfully!');
                        form.reset();

                        // Reload attachments list
                        const invoiceId = document.getElementById('attachment_invoice_id').value;
                        // loadAttachments(invoiceId);
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Upload failed'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while uploading');
                })
                .finally(() => {
                    uploadBtn.innerHTML = originalBtnText;
                    uploadBtn.disabled = false;
                });
        });

        // View invoice details
        function viewInvoiceDetails(invoiceId) {
            window.open(`{{ url('manager/invoices') }}/${invoiceId}`, '_blank');
        }


        // Download TDS proof by tax ID
        async function downloadTdsProofById(taxId) {
            // Get the button that was clicked
            const button = event.target;
            const originalText = button.innerHTML;

            // Show loading indicator
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;

            try {
                // Create download URL - FIXED: Use correct route
                const downloadUrl = `https://xhtmlreviews.in/beta-finance/manager/taxes/${taxId}/download-tds-proof`;

                // Create temporary iframe
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.name = 'tds_download_frame';
                iframe.id = 'tds_download_frame';

                // Add event listener for load to check for errors
                iframe.onload = function () {
                    setTimeout(() => {
                        // Check if iframe has content (error page)
                        try {
                            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                            const bodyText = iframeDoc.body.innerText;

                            if (bodyText.includes('404') || bodyText.includes('Error')) {
                                console.error('Download error:', bodyText);
                                alert('Error downloading file: ' + bodyText);
                            }
                        } catch (e) {
                            // Cross-origin error, assume download started
                        }
                    }, 1000);
                };

                // Set source and add to document
                iframe.src = downloadUrl;
                document.body.appendChild(iframe);

                // Alternative: Use fetch to check if file exists first
                try {
                    const response = await fetch(downloadUrl, {
                        method: 'HEAD'
                    });
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                } catch (fetchError) {
                    console.error('File check failed:', fetchError);
                    // Continue anyway - browser might handle it
                }

            } catch (error) {
                console.error('Download error:', error);
                alert('Error downloading TDS proof: ' + error.message);
            } finally {
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;

                    // Remove iframe if it exists
                    const existingIframe = document.getElementById('tds_download_frame');
                    if (existingIframe) {
                        document.body.removeChild(existingIframe);
                    }
                }, 3000);
            }
        }

        // Alternative using direct link click
        function downloadTdsProofById(taxId) {
            const downloadUrl = `{{ url('manager/taxes') }}/${taxId}/download-tds-proof`;

            // Create a temporary link and click it
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.target = '_blank';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        // Alternative: Direct download with fetch
        function downloadTdsProofDirect(taxId) {
            // Show loading
            const originalText = event.target.innerHTML;
            event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            event.target.disabled = true;

            fetch(`{{ url('https://xhtmlreviews.in/beta-finance/manager/taxes') }}/${taxId}/download-tds-proof`, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => {
                    if (response.ok) {
                        return response.blob();
                    }
                    throw new Error('Download failed');
                })
                .then(blob => {
                    // Create download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;

                    // Get filename from response headers or generate one
                    const contentDisposition = response.headers.get('content-disposition');
                    let filename = `tds_proof_${taxId}.pdf`;
                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                        if (filenameMatch) {
                            filename = filenameMatch[1];
                        }
                    }

                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();

                    // Cleanup
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to download TDS proof');
                })
                .finally(() => {
                    // Reset button
                    event.target.innerHTML = originalText;
                    event.target.disabled = false;
                });
        }

        function updatePerPage() {
            const perPage = document.getElementById('perPageSelector').value;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', perPage);
            url.searchParams.set('page', 1); // Reset to page 1 when changing per page
            window.location.href = url.toString();
        }
    </script>
@endsection