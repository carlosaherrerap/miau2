<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permisosp;
use App\Http\Requests\PermisoRequest;
class PermisoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PermisoRequest $request)
    {
        $validated = $request->validated();

        $new_permiso = Permisosp::create($validated);
        return response()->json($new_permiso, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //$permiso=Permisosp::with(['fk_publicacion','fk_usuario','fk_rol','fk_sedereg','fk_sedejuris'])->findOrFail($id);
        $permiso = Permisosp::findOrFail($id);

        $permiso_available = null;

        if (!is_null($permiso->id_usuario)) {
            $permiso_available = 'fk_usuario';
        } elseif (!is_null($permiso->id_rol)) {
            $permiso_available = 'fk_rol';
        } elseif (!is_null($permiso->id_sedereg)) {
            $permiso_available = 'fk_sedereg';
        } elseif (!is_null($permiso->id_sedejuris)) {
            $permiso_available = 'fk_sedejuris';
        }

        if ($permiso_available) {
            $permiso->load('fk_publicacion');
            $permiso->load($permiso_available);
        }

        return response()->json($permiso, 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PermisoRequest $request, string $id)
    {
        $permiso = Permisosp::findOrFail($id);

        $validated = $request->validated();
        $permiso->update($validated);

        return response()->json($permiso, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $permiso = Permisosp::findOrFail($id);
        $permiso->delete();
        return response()->json(["message" => 'Se removió el permiso'], 201);
    }
}
