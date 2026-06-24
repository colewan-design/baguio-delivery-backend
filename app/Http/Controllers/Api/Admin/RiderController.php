<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
            'password' => ['required', 'string', 'min:8'],
            'vehicle_type' => ['nullable', 'string', 'max:255'],
        ]);

        $rider = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'role' => 'rider',
            ]);

            return Rider::create([
                'user_id' => $user->id,
                'vehicle_type' => $data['vehicle_type'] ?? null,
                'status' => 'offline',
            ]);
        });

        return response()->json($rider->load('user'), 201);
    }
}
