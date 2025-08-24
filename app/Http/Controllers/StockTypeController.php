<?php

namespace App\Http\Controllers;

use App\Models\StockType;
use Illuminate\Http\Request;

class StockTypeController extends Controller
{
    public function index()
    {
        $stockTypes = StockType::all();
        return view('production.stock_types', compact('stockTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:stock_types,id',
            'flow_stage' => 'required|integer',
            'decrease_from' => 'nullable|integer',
            'increase_to' => 'nullable|integer',
            'decrease_amount' => 'nullable|numeric',
            'increase_amount' => 'nullable|numeric',
        ]);

        StockType::create($validated);

        return redirect()->route('stock_types.index')
               ->with('success', 'Stock type created successfully.');
    }

    public function update(Request $request, StockType $stockType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:stock_types,id',
            'flow_stage' => 'required|integer',
            'decrease_from' => 'nullable|integer',
            'increase_to' => 'nullable|integer',
            'decrease_amount' => 'nullable|numeric',
            'increase_amount' => 'nullable|numeric',
        ]);

        $stockType->update($validated);

        return redirect()->route('stock_types.index')
               ->with('success', 'Stock type updated successfully.');
    }
}