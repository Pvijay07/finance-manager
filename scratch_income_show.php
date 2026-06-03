    public function showIncome($id)
    {
        try {
            $income = Income::with(['company', 'invoice', 'parent.children', 'children', 'taxes'])->find($id);
            
            if (!$income) {
                return redirect()->back()->with('error', 'Income not found');
            }

            // Calculate family totals
            $familyIncomes = collect([$income]);
            if ($income->parent) {
                $familyIncomes = $familyIncomes->merge([$income->parent])->merge($income->parent->children);
            } else {
                $familyIncomes = $familyIncomes->merge($income->children);
            }
            
            $uniqueFamily = $familyIncomes->unique('id');
            $original_conversion_cost = $uniqueFamily->sum('conversion_cost');
            $original_total_amount = $uniqueFamily->sum('amount');
            
            return view('Manager.cash-flow.income_view', compact('income', 'uniqueFamily', 'original_conversion_cost', 'original_total_amount'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load income details: ' . $e->getMessage());
        }
    }
