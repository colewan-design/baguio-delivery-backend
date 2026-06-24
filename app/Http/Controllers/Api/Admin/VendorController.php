<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AccountActivationMail;
use App\Models\Lead;
use App\Models\User;
use App\Models\Vendor;
use App\Support\ActivationLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    public function index(Request $request)
    {
        $query = Vendor::query()->with('user');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'business_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'vendor_phone' => ['required', 'string'],
            'opens_at' => ['nullable'],
            'closes_at' => ['nullable'],
            'logo_url' => ['nullable', 'string'],
            'lead_id' => ['nullable', 'exists:leads,id'],
        ]);

        $vendor = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make(Str::random(40)),
                'role' => 'vendor',
            ]);

            $vendor = Vendor::create([
                'user_id' => $user->id,
                'lead_id' => $data['lead_id'] ?? null,
                'business_name' => $data['business_name'],
                'address' => $data['address'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'phone' => $data['vendor_phone'],
                'opens_at' => $data['opens_at'] ?? null,
                'closes_at' => $data['closes_at'] ?? null,
                'logo_url' => $data['logo_url'] ?? null,
                'status' => 'approved',
            ]);

            if (! empty($data['lead_id'])) {
                Lead::whereKey($data['lead_id'])->update(['status' => 'converted']);
            }

            return $vendor;
        });

        Mail::to($vendor->user->email)->send(
            new AccountActivationMail($vendor->user, ActivationLink::for($vendor->user), 'vendor')
        );

        return response()->json($vendor->load('user'), 201);
    }

    public function update(Request $request, Vendor $vendor)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'approved', 'suspended'])],
        ]);

        $vendor->update($data);

        return response()->json($vendor->fresh());
    }
}
