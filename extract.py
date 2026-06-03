import os, re

src_dir = "/home/u506206132/domains/xhtmlreviews.in/public_html/beta-finance/ca_view_company_locked_module"
dst_dir = "/home/u506206132/domains/xhtmlreviews.in/public_html/beta-finance/resources/views/CA"

files_to_convert = {
    "ca_records_locked.html": "records.blade.php",
    "ca_expense_taxes_locked.html": "expense_taxes.blade.php",
    "ca_loans_issued_locked.html": "loans_issued.blade.php",
    "ca_loan_recovery_locked.html": "loan_recovery.blade.php",
    "ca_salary_packs_locked.html": "salary_packs.blade.php",
    "ca_tasks_locked.html": "tasks.blade.php",
    "ca_dashboard_locked.html": "dashboard.blade.php",
    "ca_statements_locked.html": "statements.blade.php",
    "ca_invoices_repository_locked.html": "invoices.blade.php"
}

for src, dst in files_to_convert.items():
    src_path = os.path.join(src_dir, src)
    if not os.path.exists(src_path):
        continue
    
    with open(src_path, "r", encoding="utf-8") as f:
        content = f.read()
    
    parts = content.split("</a></div>\n      </div>\n    </div>")
    if len(parts) < 2:
        parts = content.split("</a></div>\\n      </div>\\n    </div>")
    
    if len(parts) < 2:
        parts = content.split("</a></div>")
        if len(parts) >= 2:
            body_content = parts[-1].split("</section>")[0]
            body_content = re.sub(r"^\s*</div>\s*</div>\s*", "", body_content)
        else:
            body_content = "ERROR"
    else:
        body_content = parts[1].split("</section>")[0].strip()
        body_content = re.sub(r"^\s*</div>\s*</div>\s*", "", body_content)

    final_content = "@extends('CA.layouts.app')\n\n@section('content')\n" + body_content.strip() + "\n@endsection\n"
    
    with open(os.path.join(dst_dir, dst), "w", encoding="utf-8") as f:
        f.write(final_content)
    print(f"Created {dst}")
