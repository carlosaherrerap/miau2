<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Estadot;
use App\Http\Requests\EstadoRequest;

class EstadotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tickets = Estadot::with('fk_ticket')->get();
        return response()->json($tickets, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EstadoRequest $request)
    {

        $validated = $request->validated();

        $new_estadot = Estadot::create($validated);

        return response()->json($new_estadot, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $estado = Estadot::with('fk_ticket')->findOrFail($id);
        return response()->json($estado, 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EstadoRequest $request, string $id)
    {
        $estado = Estadot::findOrFail($id);

        $validated = $request->validated();

        $estado->update($validated);

        return response()->json($estado, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $estado = Estadot::findOrFail($id);
        $estado->delete();
        return response()->json(["message" => "Estado eliminado"], 200);
    }
}
