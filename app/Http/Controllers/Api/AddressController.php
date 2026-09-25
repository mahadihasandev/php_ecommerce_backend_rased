<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->get('user_id') ?? optional($request->user())->id;

        $addresses = Address::when($userId, function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->get();

        return response()->json($addresses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'address' => 'required|string',
            'city' => 'required|string',
            'District' => 'nullable|string',
            'state' => 'nullable|string',
            'zip' => 'nullable|string',
            'phone' => 'nullable|string',
            'default' => 'nullable|boolean',
        ]);

        if (! empty($validated['default'])) {
            Address::where('user_id', optional($request->user())->id)
                ->update(['default' => false]);
        }

        $address = Address::create([
            ...$validated,
            'user_id' => optional($request->user())->id ?? $request->get('user_id'),
            '_id' => 'addr-' . uniqid(),
        ]);

        return response()->json($address, 201);
    }
}
