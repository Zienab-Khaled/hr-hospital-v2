<?php

namespace App\Http\Controllers;

use App\Models\DebtInventory;
use App\Models\Patient;
use Illuminate\Http\Request;

class DebtInventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('procedures.debt_inventory');
        $items = DebtInventory::with('patient')->latest()->paginate(15);
        return view('debt-inventories.index', compact('items'));
    }

    public function create()
    {
        $this->authorize('procedures.debt_inventory');
        $patients = Patient::orderBy('name')->get();
        return view('debt-inventories.create', compact('patients'));
    }

    public function store(Request $request)
    {
        $this->authorize('procedures.debt_inventory');
        $valid = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'inventory_date' => 'required|date',
            'total_debt' => 'required|numeric|min:0',
            'details' => 'nullable|string',
        ]);
        $valid['created_by'] = auth()->id();
        DebtInventory::create($valid);
        return redirect()->route('debt-inventories.index')->with('success', __('Saved successfully.'));
    }
}
