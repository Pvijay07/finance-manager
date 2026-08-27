<!-- Change Password Modal Component -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content" style="border-radius: 18px; border: 1px solid #e2e8f0; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; background: #ffffff;">
            
            <!-- Modal Header -->
            <div class="modal-header px-4 py-3" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; border-bottom: none;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: rgba(99, 102, 241, 0.2); display: flex; align-items: center; justify-content: center; color: #818cf8; font-size: 1.25rem;">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="changePasswordModalLabel" style="font-weight: 700; font-size: 1.15rem; color: #ffffff;">Change Password</h5>
                        <p class="mb-0 text-slate-400" style="font-size: 0.8rem; color: #94a3b8;">Update your account credentials</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1); opacity: 0.7;"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4" style="background: #ffffff;">
                <form id="globalChangePasswordForm" method="POST" action="{{ route('password.change') }}">
                    @csrf

                    <!-- Alert message container -->
                    <div id="cp_modal_alert" class="alert d-none mb-3" style="border-radius: 10px; font-size: 0.88rem; padding: 10px 14px;"></div>

                    <!-- Current Password -->
                    <div class="mb-3">
                        <label for="cp_current_password" class="form-label" style="font-weight: 600; font-size: 0.88rem; color: #334155;">
                            Current Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group" style="position: relative;">
                            <span class="input-group-text" style="background: #f8fafc; border-color: #cbd5e1; color: #64748b; border-top-left-radius: 10px; border-bottom-left-radius: 10px;">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="cp_current_password" name="current_password" placeholder="Enter current password" required style="border-color: #cbd5e1; font-size: 0.92rem; padding: 10px 12px;">
                            <button type="button" class="btn btn-outline-secondary" id="cp_toggle_current" style="border-color: #cbd5e1; border-top-right-radius: 10px; border-bottom-right-radius: 10px; color: #64748b;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="cp_current_error" class="text-danger small mt-1 d-none"></div>
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <label for="cp_new_password" class="form-label" style="font-weight: 600; font-size: 0.88rem; color: #334155;">
                            New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group" style="position: relative;">
                            <span class="input-group-text" style="background: #f8fafc; border-color: #cbd5e1; color: #64748b; border-top-left-radius: 10px; border-bottom-left-radius: 10px;">
                                <i class="fas fa-shield-alt"></i>
                            </span>
                            <input type="password" class="form-control" id="cp_new_password" name="password" placeholder="Min 8 chars with letters & numbers" required minlength="8" style="border-color: #cbd5e1; font-size: 0.92rem; padding: 10px 12px;">
                            <button type="button" class="btn btn-outline-secondary" id="cp_toggle_new" style="border-color: #cbd5e1; border-top-right-radius: 10px; border-bottom-right-radius: 10px; color: #64748b;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <!-- Strength Progress Bar -->
                        <div class="progress mt-2" style="height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                            <div id="cp_strength_bar" class="progress-bar" role="progressbar" style="width: 0%; transition: width 0.3s ease;"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span id="cp_strength_label" style="font-size: 0.75rem; color: #94a3b8; font-weight: 600;">Strength: Not entered</span>
                            <span style="font-size: 0.72rem; color: #94a3b8;">Min. 8 characters</span>
                        </div>
                        <div id="cp_new_error" class="text-danger small mt-1 d-none"></div>
                    </div>

                    <!-- Confirm New Password -->
                    <div class="mb-4">
                        <label for="cp_confirm_password" class="form-label" style="font-weight: 600; font-size: 0.88rem; color: #334155;">
                            Confirm New Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group" style="position: relative;">
                            <span class="input-group-text" style="background: #f8fafc; border-color: #cbd5e1; color: #64748b; border-top-left-radius: 10px; border-bottom-left-radius: 10px;">
                                <i class="fas fa-check-double"></i>
                            </span>
                            <input type="password" class="form-control" id="cp_confirm_password" name="password_confirmation" placeholder="Re-enter new password" required minlength="8" style="border-color: #cbd5e1; font-size: 0.92rem; padding: 10px 12px;">
                            <button type="button" class="btn btn-outline-secondary" id="cp_toggle_confirm" style="border-color: #cbd5e1; border-top-right-radius: 10px; border-bottom-right-radius: 10px; color: #64748b;">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="cp_match_status" class="small mt-1 d-none" style="font-weight: 600;"></div>
                        <div id="cp_confirm_error" class="text-danger small mt-1 d-none"></div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="d-flex gap-2 justify-content-end pt-2" style="border-top: 1px solid #f1f5f9;">
                        <button type="button" class="btn btn-light px-4 py-2" data-bs-dismiss="modal" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; color: #64748b; border: 1px solid #e2e8f0;">
                            Cancel
                        </button>
                        <button type="submit" id="cp_submit_btn" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2" style="border-radius: 10px; font-weight: 600; font-size: 0.9rem; background: #4f46e5; border-color: #4f46e5; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                            <span id="cp_spinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <i class="fas fa-save" id="cp_btn_icon"></i>
                            <span id="cp_btn_text">Update Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function openChangePasswordModal() {
        const form = document.getElementById('globalChangePasswordForm');
        if (form) {
            form.reset();
            document.getElementById('cp_strength_bar').style.width = '0%';
            document.getElementById('cp_strength_label').textContent = 'Strength: Not entered';
            document.getElementById('cp_strength_label').style.color = '#94a3b8';
            document.getElementById('cp_match_status').classList.add('d-none');
            
            // Clear any error states
            ['cp_current_error', 'cp_new_error', 'cp_confirm_error'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.textContent = '';
                    el.classList.add('d-none');
                }
            });
            const alertEl = document.getElementById('cp_modal_alert');
            if (alertEl) {
                alertEl.className = 'alert d-none';
                alertEl.textContent = '';
            }
        }

        const modalEl = document.getElementById('changePasswordModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modalInstance.show();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Toggle passwords
        function setupToggle(btnId, inputId) {
            const btn = document.getElementById(btnId);
            const input = document.getElementById(inputId);
            if (btn && input) {
                btn.addEventListener('click', function() {
                    const isPwd = input.getAttribute('type') === 'password';
                    input.setAttribute('type', isPwd ? 'text' : 'password');
                    btn.innerHTML = isPwd ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
                });
            }
        }

        setupToggle('cp_toggle_current', 'cp_current_password');
        setupToggle('cp_toggle_new', 'cp_new_password');
        setupToggle('cp_toggle_confirm', 'cp_confirm_password');

        // Password Strength calculation
        const newPwd = document.getElementById('cp_new_password');
        const confirmPwd = document.getElementById('cp_confirm_password');
        const strengthBar = document.getElementById('cp_strength_bar');
        const strengthLabel = document.getElementById('cp_strength_label');
        const matchStatus = document.getElementById('cp_match_status');

        if (newPwd) {
            newPwd.addEventListener('input', function() {
                const val = this.value;
                let score = 0;
                if (val.length >= 8) score += 1;
                if (/[a-z]/.test(val) && /[A-Z]/.test(val)) score += 1;
                if (/\d/.test(val)) score += 1;
                if (/[^a-zA-Z\d]/.test(val)) score += 1;

                if (!val) {
                    strengthBar.style.width = '0%';
                    strengthLabel.textContent = 'Strength: Not entered';
                    strengthLabel.style.color = '#94a3b8';
                } else if (score === 1) {
                    strengthBar.style.width = '25%';
                    strengthBar.style.background = '#ef4444';
                    strengthLabel.textContent = 'Strength: Weak';
                    strengthLabel.style.color = '#ef4444';
                } else if (score === 2) {
                    strengthBar.style.width = '50%';
                    strengthBar.style.background = '#f59e0b';
                    strengthLabel.textContent = 'Strength: Fair';
                    strengthLabel.style.color = '#f59e0b';
                } else if (score === 3) {
                    strengthBar.style.width = '75%';
                    strengthBar.style.background = '#3b82f6';
                    strengthLabel.textContent = 'Strength: Good';
                    strengthLabel.style.color = '#3b82f6';
                } else if (score >= 4) {
                    strengthBar.style.width = '100%';
                    strengthBar.style.background = '#10b981';
                    strengthLabel.textContent = 'Strength: Strong ✓';
                    strengthLabel.style.color = '#10b981';
                }
                checkConfirmationMatch();
            });
        }

        if (confirmPwd) {
            confirmPwd.addEventListener('input', checkConfirmationMatch);
        }

        function checkConfirmationMatch() {
            if (!confirmPwd || !newPwd) return;
            const p1 = newPwd.value;
            const p2 = confirmPwd.value;

            if (!p2) {
                matchStatus.classList.add('d-none');
                return;
            }

            matchStatus.classList.remove('d-none');
            if (p1 === p2) {
                matchStatus.innerHTML = '<i class="fas fa-check-circle me-1"></i> Passwords match';
                matchStatus.style.color = '#10b981';
            } else {
                matchStatus.innerHTML = '<i class="fas fa-times-circle me-1"></i> Passwords do not match';
                matchStatus.style.color = '#ef4444';
            }
        }

        // Form AJAX Handler
        const form = document.getElementById('globalChangePasswordForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Clear errors
                ['cp_current_error', 'cp_new_error', 'cp_confirm_error'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.textContent = '';
                        el.classList.add('d-none');
                    }
                });

                const alertEl = document.getElementById('cp_modal_alert');
                alertEl.className = 'alert d-none';
                alertEl.textContent = '';

                // Client validation
                if (newPwd.value.length < 8) {
                    const err = document.getElementById('cp_new_error');
                    err.textContent = 'Password must be at least 8 characters long.';
                    err.classList.remove('d-none');
                    return;
                }

                if (newPwd.value !== confirmPwd.value) {
                    const err = document.getElementById('cp_confirm_error');
                    err.textContent = 'Passwords do not match.';
                    err.classList.remove('d-none');
                    return;
                }

                // Loading state
                const submitBtn = document.getElementById('cp_submit_btn');
                const spinner = document.getElementById('cp_spinner');
                const btnIcon = document.getElementById('cp_btn_icon');
                const btnText = document.getElementById('cp_btn_text');

                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                btnIcon.classList.add('d-none');
                btnText.textContent = 'Updating...';

                const formData = new FormData(form);

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value;

                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Close modal
                        const modalEl = document.getElementById('changePasswordModal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) modalInstance.hide();
                        }

                        form.reset();

                        // SweetAlert2 or standard alert
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Password Changed!',
                                text: data.message || 'Your password has been successfully updated.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            alert(data.message || 'Password updated successfully!');
                        }
                    } else {
                        // Handle validation errors
                        if (data.errors) {
                            if (data.errors.current_password) {
                                const err = document.getElementById('cp_current_error');
                                err.textContent = data.errors.current_password[0];
                                err.classList.remove('d-none');
                            }
                            if (data.errors.password) {
                                const err = document.getElementById('cp_new_error');
                                err.textContent = data.errors.password[0];
                                err.classList.remove('d-none');
                            }
                            if (data.errors.password_confirmation) {
                                const err = document.getElementById('cp_confirm_error');
                                err.textContent = data.errors.password_confirmation[0];
                                err.classList.remove('d-none');
                            }
                        } else {
                            alertEl.className = 'alert alert-danger';
                            alertEl.textContent = data.message || 'An error occurred while updating your password. Please try again.';
                            alertEl.classList.remove('d-none');
                        }
                    }
                } catch (error) {
                    alertEl.className = 'alert alert-danger';
                    alertEl.textContent = 'Network error. Please check your connection and try again.';
                    alertEl.classList.remove('d-none');
                } finally {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    btnIcon.classList.remove('d-none');
                    btnText.textContent = 'Update Password';
                }
            });
        }
    });
</script>
