<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoriaAtencion;
use App\Models\TipoAtencion;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    use ApiResponse;

    /**
     * Listar categorias con sus tipos
     */
    public function index(Request $request)
    {
        $soloActivos = $request->get('solo_activos', 'true') === 'true';

        $query = CategoriaAtencion::with(['tipos' => function ($q) use ($soloActivos) {
            if ($soloActivos) {
                $q->where('activo', true);
            }
            $q->orderBy('nombre', 'asc');
        }]);

        if ($soloActivos) {
            $query->where('activo', true);
        }

        $categorias = $query->orderBy('nombre', 'asc')->get();

        return $this->successResponse($categorias);
    }

    /**
     * Crear Categoria
     */
    public function storeCategoria(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:categoria_atencion,nombre',
            'activo' => 'boolean'
        ]);

        $categoria = CategoriaAtencion::create([
            'nombre' => $validated['nombre'],
            'activo' => $validated['activo'] ?? true
        ]);

        return $this->successResponse($categoria, 'Categoría creada exitosamente.', 201);
    }

    /**
     * Actualizar Categoria
     */
    public function updateCategoria(Request $request, string $id)
    {
        $categoria = CategoriaAtencion::findOrFail($id);

        $validated = $request->validate([
            'nombre' => "required|string|max:255|unique:categoria_atencion,nombre,{$id}",
            'activo' => 'boolean'
        ]);

        $categoria->update($validated);

        return $this->successResponse($categoria, 'Categoría actualizada correctamente.');
    }

    /**
     * Activar / Desactivar Categoria
     */
    public function toggleCategoria(Request $request, string $id)
    {
        $categoria = CategoriaAtencion::findOrFail($id);
        $categoria->activo = !$categoria->activo;
        $categoria->save();

        $estado = $categoria->activo ? 'activada' : 'desactivada';
        return $this->successResponse($categoria, "Categoría {$estado} correctamente.");
    }

    /**
     * Crear Tipo de Atencion
     */
    public function storeTipo(Request $request)
    {
        $validated = $request->validate([
            'id_categoria' => 'required|integer|exists:categoria_atencion,id',
            'nombre' => 'required|string|max:255',
            'activo' => 'boolean'
        ]);

        $tipo = TipoAtencion::create([
            'id_categoria' => $validated['id_categoria'],
            'nombre' => $validated['nombre'],
            'activo' => $validated['activo'] ?? true
        ]);

        return $this->successResponse($tipo, 'Tipo de atención creado exitosamente.', 201);
    }

    /**
     * Actualizar Tipo de Atencion
     */
    public function updateTipo(Request $request, string $id)
    {
        $tipo = TipoAtencion::findOrFail($id);

        $validated = $request->validate([
            'id_categoria' => 'required|integer|exists:categoria_atencion,id',
            'nombre' => 'required|string|max:255',
            'activo' => 'boolean'
        ]);

        $tipo->update($validated);

        return $this->successResponse($tipo, 'Tipo de atención actualizado correctamente.');
    }

    /**
     * Activar / Desactivar Tipo de Atencion
     */
    public function toggleTipo(Request $request, string $id)
    {
        $tipo = TipoAtencion::findOrFail($id);
        $tipo->activo = !$tipo->activo;
        $tipo->save();

        $estado = $tipo->activo ? 'activado' : 'desactivado';
        return $this->successResponse($tipo, "Tipo de atención {$estado} correctamente.");
    }
}
