<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AccountActivationMail;
use App\Models\Lead;
use App\Models\Rider;
use App\Models\User;
use App\Support\ActivationLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RiderController extends Controller
{
    public function index(Request $request)
    {
        $query = Rider::query()->with('user');

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
            'vehicle_type' => ['nullable', 'string', 'max:255'],
            'lead_id' => ['nullable', 'exists:leads,id'],
        ]);

        $rider = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make(Str::random(40)),
                'role' => 'rider',
            ]);

            $rider = Rider::create([
                'user_id' => $user->id,
                'lead_id' => $data['lead_id'] ?? null,
                'vehicle_type' => $data['vehicle_type'] ?? null,
                'status' => 'offline',
            ]);

            if (! empty($data['lead_id'])) {
                Lead::whereKey($data['lead_id'])->update(['status' => 'converted']);
            }

            return $rider;
        });

        Mail::to($rider->user->email)->send(
            new AccountActivationMail($rider->user, ActivationLink::for($rider->user), 'rider')
        );

        return response()->json($rider->load('user'), 201);
    }
}
