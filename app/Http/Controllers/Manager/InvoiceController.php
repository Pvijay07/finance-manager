<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        return redirect()->route('income.index');
    }

    public function store(Request $request)
    {
        return redirect()->route('income.index');
    }

    public function markPaid($invoice)
    {
        return redirect()->route('income.index');
    }
}
