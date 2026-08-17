<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asignacion;
use Illuminate\Http\Request;
use App\Http\Requests\AsignacionRequest;

class AsignacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AsignacionRequest $request)
    {
        $validate = $request->validated();

        $new_asignacion = Asignacion::create($validate);

        return response()->json($new_asignacion, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $asignacion = Asignacion::findOrFail($id);

        $asignacion_activa = null;

        if (!is_null($asignacion->id_usuario)) {
            $asignacion_activa = 'fk_usuario'; //fk relacion en el modelo
        } elseif (!is_null($asignacion->id_rol)) {
            $asignacion_activa = 'fk_rol';
        } elseif (!is_null($asignacion->id_sedereg)) {
            $asignacion_activa = 'fk_sedereg';
        } elseif (!is_null($asignacion->id_sedejuris)) {
            $asignacion_activa = 'fk_sedejuris';
        }

        if ($asignacion_activa) {
            $asignacion->load($asignacion_activa);
        }

        return response()->json($asignacion, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AsignacionRequest $request, string $id)
    {
        $asignacion = Asignacion::findOrFail($id);

        $validate = $request->validated();

        $asignacion->update($validate);

        return response()->json($asignacion, 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $asignacion = Asignacion::findOrFail($id);
        $asignacion->delete();
        return response()->json(["message" => "Se quitó la asignación correctamente"], 200);
    }
}
