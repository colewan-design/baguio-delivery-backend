<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['vendor', 'rider'])],
            'name' => ['required', 'string', 'max:255'],
            'contact' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        $lead = Lead::create($data);

        return response()->json($lead, 201);
    }
}
