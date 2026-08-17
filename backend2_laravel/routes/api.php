<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IncidenciaInformativaController;
use App\Http\Controllers\Api\PublicacionController;
use App\Http\Controllers\Api\SedeController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UsuarioController;

// 1. Rutas Publicas
Route::post('/auth/login', [AuthController::class, 'login']);

// 2. Rutas Protegidas por Autenticacion Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Sedes
    Route::get('/sedes/regionales', [SedeController::class, 'regionales']);
    Route::get('/sedes/jurisdiccionales', [SedeController::class, 'jurisdiccionales']);

    // Catalogo de Categorias y Tipos de Atencion
    Route::get('/catalogo', [CatalogoController::class, 'index']);
    Route::post('/catalogo/categorias', [CatalogoController::class, 'storeCategoria']);
    Route::put('/catalogo/categorias/{id}', [CatalogoController::class, 'updateCategoria']);
    Route::patch('/catalogo/categorias/{id}/toggle', [CatalogoController::class, 'toggleCategoria']);
    Route::post('/catalogo/tipos', [CatalogoController::class, 'storeTipo']);
    Route::put('/catalogo/tipos/{id}', [CatalogoController::class, 'updateTipo']);
    Route::patch('/catalogo/tipos/{id}/toggle', [CatalogoController::class, 'toggleTipo']);

    // Tickets de Atencion
    Route::get('/tickets/indicador-pendientes', [TicketController::class, 'indicadorPendientes']);
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::put('/tickets/{id}/atender', [TicketController::class, 'atender']);
    Route::put('/tickets/{id}/clasificacion', [TicketController::class, 'clasificacion']);

    // Dashboard y Modulo Gerencial
    Route::get('/dashboard/kpis', [DashboardController::class, 'kpis']);
    Route::get('/dashboard/gerencial', [DashboardController::class, 'gerencial']);

    // Avisos Operativos e Incidencias Informativas (Cortes, Conectividad, Moviles)
    Route::get('/incidencias-informativas', [IncidenciaInformativaController::class, 'index']);
    Route::post('/incidencias-informativas', [IncidenciaInformativaController::class, 'store']);
    Route::get('/incidencias-informativas/{id}', [IncidenciaInformativaController::class, 'show']);
    Route::put('/incidencias-informativas/{id}', [IncidenciaInformativaController::class, 'update']);

    // Publicaciones de Aplicativos y Credenciales
    Route::get('/publicaciones', [PublicacionController::class, 'index']);
    Route::get('/publicaciones/mis-publicaciones', [PublicacionController::class, 'misPublicaciones']);
    Route::post('/publicaciones', [PublicacionController::class, 'store']);
    Route::put('/publicaciones/{id}', [PublicacionController::class, 'update']);
    Route::post('/publicaciones/{id}/credenciales-csv', [PublicacionController::class, 'cargarCredencialesCsv']);
    Route::get('/publicaciones/{id}/seguimiento', [PublicacionController::class, 'seguimiento']);

    // Usuarios y Carga Masiva
    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::post('/usuarios/carga-masiva', [UsuarioController::class, 'cargaMasiva']);
});