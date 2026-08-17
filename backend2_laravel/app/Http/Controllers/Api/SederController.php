<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sedereg;
use App\Http\Requests\SederRequest;

class SederController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sede_Reg = Sedereg::All();
        return response()->json($sede_Reg, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SederRequest $request)
    {

        $validated = $request->validated();
        $new_sedereg = Sedereg::create($validated);
        return response()->json($new_sedereg, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sede_Reg = Sedereg::findOrFail($id);
        return response()->json($sede_Reg, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SederRequest $request, string $id)
    {
        $sede_Reg = Sedereg::findOrFail($id);
        $validated = $request->validated();
        $sede_Reg->update($validated);

        return response()->json($sede_Reg, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sede_Reg = Sedereg::findOrFail($id);
        $sede_Reg->delete();
        return response()->json(["message" => "Se eliminó correctamente"], 200);
    }
}
