<?php

namespace App\Http\Controllers\Api\Vendor;

use App\Http\Controllers\Controller;
use App\Mail\AccountActivationMail;
use App\Models\User;
use App\Models\Vendor;
use App\Support\ActivationLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'business_name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['food', 'groceries', 'pharmacy', 'errands'])],
            'address' => ['required', 'string'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'vendor_phone' => ['required', 'string'],
        ]);

        $vendor = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make(Str::random(40)),
                'role' => 'vendor',
            ]);

            return Vendor::create([
                'user_id' => $user->id,
                'business_name' => $data['business_name'],
                'category' => $data['category'],
                'address' => $data['address'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'phone' => $data['vendor_phone'],
            ]);
        });

        Mail::to($vendor->user->email)->send(
            new AccountActivationMail($vendor->user, ActivationLink::for($vendor->user), 'vendor')
        );

        return response()->json($vendor->load('user'), 201);
    }
}
