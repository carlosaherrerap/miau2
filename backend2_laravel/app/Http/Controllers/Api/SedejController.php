<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sedejuris;
use App\Http\Requests\SedejRequest;

class SedejController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sedes_juris = Sedejuris::All();
        return response()->json($sedes_juris, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SedejRequest $request)
    {

        $validated = $request->validated();
        $new_sedejuris = Sedejuris::create($validated);
        return response()->json($new_sedejuris, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sede_juris = Sedejuris::findOrFail($id);
        return response()->json($sede_juris, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SedejRequest $request, string $id)
    {
        $sede_juris = Sedejuris::findOrFail($id);
        $validated = $request->validated();
        $sede_juris->update($validated);
        return response()->json($sede_juris, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $sede_juris = Sedejuris::findOrFail($id);
        $sede_juris->delete();
        return response()->json($sede_juris, 200);
    }
}
