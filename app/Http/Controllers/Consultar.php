<?php

namespace App\Http\Controllers;

use App\Models\HistorialConsulta;
use App\Models\Plan;
use App\Models\Suscripcion;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Consultar extends Controller
{
    use ApiResponses;
    public function consultar(Request $request)
    {
        $validated = $request->validate([
            'identificacion' => ['required', 'digits:10']
        ]);

        $user = $request->user();
        $cedula = $validated['identificacion'];
        $suscripcion = $user->suscripcion;

        if (!$suscripcion || $suscripcion->consultas_disponibles <= 0) {
            return $this->errorResponse('Saldo insuficiente o sin suscripción', 403);
        }

        try {

            $consultaPrevia = HistorialConsulta::where('cedula_consultada', $cedula)->first();
            if ($consultaPrevia) {
                $data = $consultaPrevia->resultado_json;
            } else {
                $response = Http::get('https://apiconsult.zampisoft.com/api/consultar', [
                    'identificacion' => $cedula,
                    'token' => env('ZAMPISOFT_TOKEN')
                ]);

                if ($response->failed()) {
                    return $this->errorResponse('Error al consultar el servicio externo.', 502);
                }
                $data = $response->json();
            }

            DB::transaction(function () use ($suscripcion, $user, $cedula, $data, $request) {

                $suscripcion->decrement('consultas_disponibles');
                HistorialConsulta::create([
                    'user_id' => $user->id,
                    'cedula_consultada' => $cedula,
                    'resultado_json' => $data,
                    'ip_address' => $request->ip()
                ]);
            });
            return $this->successResponse($data, 'Consulta realizada con éxito');
        } catch (Exception $e) {
            return $this->errorResponse('Ocurrió un error inesperado: ' . $e->getMessage(), 500);
        }
    }

    public function informacionsuscripcion(Request $request)
    {
        $user = $request->user();

        $suscripcion = $user->suscripcion;

        if (!$suscripcion) {
            return $this->errorResponse('No se encontró una suscripción activa', 404);
        }

        $suscripcion->load('plan');
        return $this->successResponse($suscripcion, 'Información de Suscripción recuperada', 200);
    }

    public function historialconsulta(Request $request)
    {
        $user = $request->user();

        $historial = $user->historialconsulta()->latest()->get();

        if ($historial->isEmpty()) {
            return $this->errorResponse('No se encontró historial de consultas', 404);
        }
        return $this->successResponse($historial, 'Historial de consultas recuperado', 200);
    }

    public function planes()
    {
        $planes = Plan::select('id', 'name', 'price', 'limit_consultas')->get();
        return $this->successResponse($planes, 'Planes recuperados exitosamente',200);
    }
    

}

