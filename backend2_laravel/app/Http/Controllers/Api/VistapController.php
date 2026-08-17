<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vistap;
use App\Http\Requests\VistapRequest;

class VistapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vista = Vistap::with(['fk_usuario', 'fk_publicacion'])->get();
        return response()->json($vista, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VistapRequest $request)
    {
        $validated = $request->validated();

        $newVista = Vistap::create($validated);
        return response()->json($newVista, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vista = Vistap::findOrFail($id);
        return response()->json($vista, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VistapRequest $request, string $id)
    {
        $vista = Vistap::findOrFail($id);

        $validated = $request->validated();
        $vista->update($validated);
        return response()->json($vista, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vista = Vistap::findOrFail($id);
        $vista->delete();
        return response()->json($vista, 200);
    }
}
