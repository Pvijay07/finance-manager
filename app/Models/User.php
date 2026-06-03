<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Auditable;
class User extends Authenticatable
{
    use HasFactory, Notifiable, Auditable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'company_id',
        'status',
        'last_login_at'
    ];

    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['last_login_at' => 'datetime', 'permissions' => 'array',];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function assignedComplianceTasks()
    {
        return $this->hasMany(ComplianceTask::class, 'assigned_to');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Scopes
    public function scopeManagers($query)
    {
        return $query->where('role', 'manager');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeCAs($query)
    {
        return $query->where('role', 'ca');
    }

    // Methods
    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }

        return $this->role === $roles;
    }

    public function canAccessCompany($companyId)
    {
        if ($this->role === 'admin') return true;
        if ($this->role === 'ca') return true;
        return $this->company_id == $companyId;
    }

    public function hasPermission($permission)
    {
        if ($this->role === 'admin') {
            return true;
        }

        $userPermissions = $this->permissions ?? [];
        $rolePermissions = RolePermission::where('role', $this->role)->first();

        if ($rolePermissions) {
            $userPermissions = array_merge($userPermissions, $rolePermissions->permissions);
        }

        return in_array($permission, $userPermissions);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getRoleBadgeAttribute()
    {
        $badges = [
            'admin'   => 'role-admin',
            'manager' => 'role-manager',
            'user'    => 'role-user',
            'ca'      => 'role-ca'
            
        ];

        $labels = [
            'admin'   => 'Administrator',
            'manager' => 'Manager',
            'user'    => 'User',
            'ca'      => 'CA'
        ];

        return '<span class="role-badge ' . ($badges[$this->role] ?? '') . '">' . ($labels[$this->role] ?? $this->role) . '</span>';
    }
    public function isAdmin()
    {
        return $this->role === 'Admin' || $this->role === 'Super Admin';
    }
    public function isCA()
    {
        return $this->role === 'CA';
    }
}
