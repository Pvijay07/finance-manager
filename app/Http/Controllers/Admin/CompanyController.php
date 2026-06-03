<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Company, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
public function index(Request $request)
{
    $query = Company::with('manager');
    
    // Search functionality
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhereHas('manager', function($q) use ($search) {
                  $q->where('name', 'LIKE', "%{$search}%");
              });
        });
    }
    
    // Status filter
    if ($request->has('status') && in_array($request->status, ['active', 'inactive'])) {
        $query->where('status', $request->status);
    }
    
    // Sorting (optional)
    $sortBy = $request->get('sort_by', 'created_at');
    $sortOrder = $request->get('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);
    
    $companies = $query->paginate(10);
    $managers  = User::where('role', 'manager')->get();
    
    return view('Admin.company', compact('companies', 'managers'));
}
  public function store(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        // 'code'       => 'required|string|max:255|unique:companies,code',
        'name'       => 'required|string|max:255',
        'email'      => 'nullable|email|max:255',
        'manager_id' => 'nullable|integer|exists:users,id',
        'currency'   => 'required|string|max:10',
        'website'    => 'nullable|max:255',
        'address'    => 'nullable|string|max:500',
        'status'     => 'required|in:active,inactive',
        'logo'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validation failed',
          'errors'  => $validator->errors()
        ], 422);
      }

      $validated = $validator->validated();
      $validated = $this->handleLogoUpload($request, $validated);

      $company = Company::create($validated);

      return response()->json([
        'success' => true,
        'message' => 'Company created successfully!',
        'company' => $company
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to create company: ' . $e->getMessage(),
        'error'   => $e->getMessage()
      ], 500);
    }
  }

  public function edit($id)
  {
    try {
      $company = Company::findOrFail($id);

      return response()->json([
        'success' => true,
        'company' => $company
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Company not found'
      ], 404);
    }
  }

  public function update(Request $request, $id)
  {
    try {
      $company = Company::findOrFail($id);

      $validator = Validator::make($request->all(), [
        'name'       => 'required|string|max:255',
        'email'      => 'nullable|email|max:255',
        'manager_id' => 'nullable|integer|exists:users,id',
        'currency'   => 'required|string|max:10',
        'website'    => 'nullable|url|max:255',
        'address'    => 'nullable|string|max:500',
        'status'     => 'required|in:active,inactive',
      ]);
      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validation failed',
          'errors'  => $validator->errors()
        ], 422);
      }

      $validated = $validator->validated();
      $company->update($validated);

      return response()->json([
        'success' => true,
        'message' => 'Company updated successfully!',
        'company' => $company
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update company: ' . $e->getMessage(),
        'error'   => $e->getMessage()
      ], 500);
    }
  }

  public function destroy($id)
  {
    try {
      $company = Company::findOrFail($id);

      // Check if company has any dependencies before deleting
      if ($company->expenses()->exists() || $company->incomes()->exists()) {
        return response()->json([
          'success' => false,
          'message' => 'Cannot delete company with existing records'
        ], 400);
      }

      // Delete logo if exists
      if ($company->logo) {
        Storage::disk('public')->delete($company->logo);
      }

      $company->delete();

      return response()->json([
        'success' => true,
        'message' => 'Company deleted successfully!'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete company',
        'error'   => $e->getMessage()
      ], 500);
    }
  }

  public function updateSettings(Request $request)
  {
    try {
      $validator = Validator::make($request->all(), [
        'company_id'           => 'required|exists:companies,id',
        'manager_id'           => 'nullable|exists:users,id',
        'financial_year_start' => 'required|date',
        'currency'             => 'required|string|max:10',
        'status'               => 'required|in:active,inactive'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'success' => false,
          'message' => 'Validation failed',
          'errors'  => $validator->errors()
        ], 422);
      }

      $validated = $validator->validated();
      $company = Company::findOrFail($validated['company_id']);

      $company->update([
        'manager_id'           => $validated['manager_id'],
        'financial_year_start' => $validated['financial_year_start'],
        'currency'             => $validated['currency'],
        'status'               => $validated['status']
      ]);

      return response()->json([
        'success' => true,
        'message' => 'Settings updated successfully!'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update settings: ' . $e->getMessage(),
        'error'   => $e->getMessage()
      ], 500);
    }
  }

  private function handleLogoUpload(Request $request, array $validated, Company $company = null)
  {
    if ($request->hasFile('logo')) {
      // Delete old logo if exists
      if ($company && $company->logo) {
        Storage::disk('public')->delete($company->logo);
      }

      $logoPath          = $request->file('logo')->store('company-logos', 'public');
      $validated['logo'] = $logoPath;
    } elseif ($request->has('remove_logo') && $request->remove_logo) {
      if ($company && $company->logo) {
        Storage::disk('public')->delete($company->logo);
        $validated['logo'] = null;
      }
    }

    return $validated;
  }
}
