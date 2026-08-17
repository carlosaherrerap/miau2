<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sedejuris;
use App\Models\Sedereg;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SedeController extends Controller
{
    use ApiResponse;

    /**
     * Listar sedes regionales con sus sedes jurisdiccionales
     */
    public function regionales(Request $request)
    {
        $sedes = Sedereg::with(['sedesJuris'])->orderBy('nombre', 'asc')->get();
        return $this->successResponse($sedes);
    }

    /**
     * Listar sedes jurisdiccionales (opcionalmente filtradas por regional)
     */
    public function jurisdiccionales(Request $request)
    {
        $query = Sedejuris::with(['sedeRegional']);

        if ($request->filled('id_sedereg')) {
            $query->where('id_sedereg', $request->id_sedereg);
        }

        $sedes = $query->orderBy('nombre', 'asc')->get();
        return $this->successResponse($sedes);
    }
}
