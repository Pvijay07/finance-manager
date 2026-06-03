<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
  public function index()
  {
    // Get all categories grouped by type
    $fixedExpenses = Category::where('main_type', 'expense')
      ->where('category_type', 'standard')
      ->where('sub_type', 'fixed')
      ->where('is_active', true)
      ->orderBy('name')
      ->get();

    $editableExpenses = Category::where('main_type', 'expense')
      ->where('category_type', 'standard')
      ->where('sub_type', 'editable')
      ->where('is_active', true)
      ->orderBy('name')
      ->get();

    $notStandardExpenses = Category::where('main_type', 'expense')
      ->where('category_type', 'not_standard')
      ->where('is_active', true)
      ->orderBy('name')
      ->get();

    $incomeCategories = Category::where('main_type', 'income')
      ->where('is_active', true)
      ->orderBy('name')
      ->get();

    $allCategories = Category::where('is_active', true)
      ->orderBy('main_type')
      ->orderBy('category_type')
      ->orderBy('sub_type')
      ->orderBy('name')
      ->get();

    return view('admin.categories.index', compact(
      'fixedExpenses',
      'editableExpenses',
      'notStandardExpenses',
      'incomeCategories',
      'allCategories'
    ));
  }

  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      // 'description' => 'nullable|string',
      'main_type' => 'required|in:expense,income',
      'category_type' => 'required|in:standard_fixed,standard_editable,not_standard,income',
    ]);

    // Validate type consistency
    $validTypes = Category::getValidCategoryTypes($request->main_type);
    if (!in_array($request->category_type, $validTypes)) {
      return response()->json([
        'success' => false,
        'message' => 'Invalid category type for selected main type'
      ], 422);
    }

    // Check for duplicate
    $exists = Category::where('name', $request->name)
      ->where('main_type', $request->main_type)
      ->where('category_type', $request->category_type)
      ->exists();

    if ($exists) {
      return response()->json([
        'success' => false,
        'message' => 'Category already exists for this type'
      ], 422);
    }

    $category = Category::create([
      'name' => $request->name,
      'description' => $request->description,
      'main_type' => $request->main_type,
      'category_type' => $request->category_type,
      'is_active' => true,
      'is_default' => false,
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Category created successfully',
      'category' => $category
    ]);
  }

  // Update category
  public function update(Request $request, Category $category)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      // 'description' => 'nullable|string',
      'main_type' => 'required|in:expense,income',
      'category_type' => 'required|in:standard_fixed,standard_editable,not_standard,income',
    ]);

    // Validate type consistency
    $validTypes = Category::getValidCategoryTypes($request->main_type);
    if (!in_array($request->category_type, $validTypes)) {
      return response()->json([
        'success' => false,
        'message' => 'Invalid category type for selected main type'
      ], 422);
    }

    // Check for duplicate (excluding current category)
    $exists = Category::where('name', $request->name)
      ->where('main_type', $request->main_type)
      ->where('category_type', $request->category_type)
      ->where('id', '!=', $category->id)
      ->exists();

    if ($exists) {
      return response()->json([
        'success' => false,
        'message' => 'Category already exists for this type'
      ], 422);
    }

    $category->update([
      'name' => $request->name,
      // 'description' => $request->description,
      'main_type' => $request->main_type,
      'category_type' => $request->category_type,
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Category updated successfully'
    ]);
  }
  public function getByType(Request $request)
  {
    $request->validate([
      'main_type' => 'required|in:expense,income',
      'sub_type' => 'required|in:standard,editable,variable,regular',
    ]);

    $categories = Category::where('main_type', $request->main_type)
      ->where('sub_type', $request->sub_type)
      ->active()
      ->orderBy('name')
      ->get();

    return response()->json([
      'success' => true,
      'categories' => $categories
    ]);
  }
  public function bulkUpdate(Request $request)
  {
    $request->validate([
      'type' => 'required|in:standard_fixed,standard_editable,not_standard,income',
      'categories' => 'required|array',
      'categories.*.id' => 'nullable|exists:categories,id',
      'categories.*.name' => 'required|string|max:255',
      'categories.*.description' => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {
      // Determine main_type based on category_type
      $mainType = in_array($request->type, ['standard_fixed', 'standard_editable', 'not_standard'])
        ? 'expense'
        : 'income';

      // Get existing categories for this type
      $existingCategories = Category::where('category_type', $request->type)
        ->where('main_type', $mainType)
        ->get()
        ->keyBy('id');

      $processedIds = [];

      foreach ($request->categories as $categoryData) {
        if (isset($categoryData['id']) && $existingCategories->has($categoryData['id'])) {
          // Update existing category
          $category = $existingCategories[$categoryData['id']];
          if (!$category->is_default) {
            $category->update([
              'name' => $categoryData['name'],
              'description' => $categoryData['description'] ?? null,
            ]);
          }
          $processedIds[] = $categoryData['id'];
        } else {
          // Create new category
          Category::create([
            'name' => $categoryData['name'],
            // 'description' => $categoryData['description'] ?? null,
            'main_type' => $mainType,
            'category_type' => $request->type,
            'is_active' => true,
            'is_default' => false,
          ]);
        }
      }

      // Soft delete categories that were removed (non-default ones)
      foreach ($existingCategories as $id => $category) {
        if (!in_array($id, $processedIds) && !$category->is_default) {
          $category->delete();
        }
      }

      DB::commit();

      return response()->json([
        'success' => true,
        'message' => 'Categories updated successfully'
      ]);
    } catch (\Exception $e) {
      DB::rollBack();

      return response()->json([
        'success' => false,
        'message' => 'Failed to update categories: ' . $e->getMessage()
      ], 500);
    }
  }
  public function assign(Request $request)
  {
    // Clear all existing assignments first
    DB::table('category_assignments')->truncate();

    $assign = $request->input('assign', []);

    foreach ($assign as $mainType => $categoryTypes) {
      foreach ($categoryTypes as $categoryType => $subTypes) {
        foreach ($subTypes as $subType => $categoryIds) {
          foreach ($categoryIds as $categoryId) {
            DB::table('category_assignments')->insert([
              'category_id'   => $categoryId,
              'main_type'     => $mainType,
              'category_type' => $categoryType,
              'sub_type'      => $subType,
              'created_at'    => now(),
              'updated_at'    => now(),
            ]);
          }
        }
      }
    }

    return response()->json([
      'success' => true,
      'message' => 'Category assignments saved successfully',
      'redirect' => route('admin.system-settings')
    ]);
  }





  private function bulkStore(Request $request)
  {
    DB::transaction(function () use ($request) {
      // Handle standard categories (sub_type: standard)
      if ($request->has('standard_categories')) {
        Category::where('main_type', 'standard')
          ->where('sub_type', 'standard')
          ->update(['is_active' => false]);

        foreach ($request->standard_categories as $categoryData) {
          Category::updateOrCreate(
            [
              'name'      => $categoryData['name'],
              'main_type' => 'standard',
              'sub_type'  => 'standard'
            ],
            [
              'is_active'  => true,
              'is_default' => false
            ]
          );
        }
      }

      // Handle editable expenses (main_type: standard, sub_type: editable)
      if ($request->has('editable_expenses')) {
        Category::where('main_type', 'standard')
          ->where('sub_type', 'editable')
          ->update(['is_active' => false]);

        foreach ($request->editable_expenses as $expenseData) {
          Category::updateOrCreate(
            [
              'name'      => $expenseData['name'],
              'main_type' => 'standard',
              'sub_type'  => 'editable'
            ],
            [
              'is_active'  => true,
              'is_default' => false
            ]
          );
        }
      }

      // Handle not standard categories (main_type: not_standard)
      if ($request->has('not_standard_categories')) {
        Category::where('main_type', 'not_standard')
          ->update(['is_active' => false]);

        foreach ($request->not_standard_categories as $categoryData) {
          Category::updateOrCreate(
            [
              'name'      => $categoryData['name'],
              'main_type' => 'not_standard'
            ],
            [
              'sub_type'   => 'variable',
              'is_active'  => true,
              'is_default' => false
            ]
          );
        }
      }
    });

    return response()->json([
      'success'  => true,
      'message'  => 'Categories saved successfully',
      'redirect' => route('admin.system-settings')
    ]);
  }



  public function destroy(Category $category)
  {
    // Don't delete, just deactivate
    $category->update(['is_active' => false]);

    return response()->json([
      'success' => true,
      'message' => 'Category deactivated successfully'
    ]);
  }

  public function saveSettings(Request $request)
  {
    $request->validate([
      'group'                                 => 'required|in:expense',
      'standard_categories'                   => 'nullable|array',
      'standard_categories.*.name'            => 'required|string|max:255',
      'standard_categories.*.description'     => 'nullable|string',
      'editable_expenses'                     => 'nullable|array',
      'editable_expenses.*.name'              => 'required|string|max:255',
      'not_standard_categories'               => 'nullable|array',
      'not_standard_categories.*.name'        => 'required|string|max:255',
      'not_standard_categories.*.description' => 'nullable|string',
      'auto_create_cycle_expenses'            => 'boolean',
      'allow_additional_expenses'             => 'boolean',
    ]);

    DB::transaction(function () use ($request) {
      // Handle standard categories (sub_type: standard)
      if ($request->has('standard_categories')) {
        Category::where('main_type', 'standard')
          ->where('sub_type', 'standard')
          ->update(['is_active' => false]);

        foreach ($request->standard_categories as $categoryData) {
          Category::updateOrCreate(
            [
              'name'      => $categoryData['name'],
              'main_type' => 'standard',
              'sub_type'  => 'standard'
            ],
            [
              'description' => $categoryData['description'] ?? null,
              'is_active'   => true,
              'is_default'  => false
            ]
          );
        }
      }

      // Handle editable expenses (main_type: standard, sub_type: editable)
      if ($request->has('editable_expenses')) {
        Category::where('main_type', 'standard')
          ->where('sub_type', 'editable')
          ->update(['is_active' => false]);

        foreach ($request->editable_expenses as $expenseData) {
          Category::updateOrCreate(
            [
              'name'      => $expenseData['name'],
              'main_type' => 'standard',
              'sub_type'  => 'editable'
            ],
            [
              'is_active'  => true,
              'is_default' => false
            ]
          );
        }
      }

      // Handle not standard categories (main_type: not_standard)
      if ($request->has('not_standard_categories')) {
        Category::where('main_type', 'not_standard')
          ->update(['is_active' => false]);

        foreach ($request->not_standard_categories as $categoryData) {
          Category::updateOrCreate(
            [
              'name'      => $categoryData['name'],
              'main_type' => 'not_standard'
            ],
            [
              'description' => $categoryData['description'] ?? null,
              'sub_type'    => 'variable', // Default sub_type for not_standard
              'is_active'   => true,
              'is_default'  => false
            ]
          );
        }
      }

      // Save other settings
      session([
        'expense_settings.auto_create_cycle_expenses' => $request->boolean('auto_create_cycle_expenses'),
        'expense_settings.allow_additional_expenses'  => $request->boolean('allow_additional_expenses'),
      ]);
    });

    return response()->json([
      'success'  => true,
      'message'  => 'Settings saved successfully',
      'redirect' => route('admin.categories.index')
    ]);
  }

  public function resetToDefaults()
  {
    DB::transaction(function () {
      // Deactivate all custom categories
      Category::where('is_default', false)->update(['is_active' => false]);

      // Reactivate default categories
      Category::where('is_default', true)->update(['is_active' => true]);
    });

    return response()->json([
      'success' => true,
      'message' => 'Categories reset to defaults successfully'
    ]);
  }
  public function assignCategories(Request $request)
  {
    $request->validate([
      'selected_categories'   => 'required|array',
      'selected_categories.*' => 'exists:categories,id',
      'assign_type'           => 'required|string'
    ]);

    // Parse assign_type
    $typeParts = explode('_', $request->assign_type);
    if (count($typeParts) !== 2) {
      return response()->json([
        'success' => false,
        'message' => 'Invalid type format'
      ]);
    }

    $mainType = $typeParts[0];
    $subType  = $typeParts[1];

    DB::transaction(function () use ($request, $mainType, $subType) {
      // Update selected categories
      Category::whereIn('id', $request->selected_categories)
        ->update([
          'main_type'  => $mainType,
          'sub_type'   => $subType,
          'updated_at' => now()
        ]);
    });

    return response()->json([
      'success' => true,
      'message' => count($request->selected_categories) . ' categories assigned to ' .
        ucfirst($mainType) . ' → ' . ucfirst($subType) . ' type'
    ]);
  }
}
