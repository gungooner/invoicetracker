<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        return response()->json($request->user()->clients()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $client = $request->user()->clients()->create($validated);

        return response()->json($client, 201);
    }

    public function show(Request $request, Client $client)
    {
        if ($client->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        return $client;
    }

    public function update(Request $request, Client $client)
    {
        if ($client->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name'    => 'sometimes|required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string',
        ]);

        $client->update($validated);

        return $client;
    }

    public function destroy(Request $request, Client $client)
    {
        if ($client->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $client->delete();

        return response()->json(null, 204);
    }
}