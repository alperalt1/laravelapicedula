<?php

namespace App\Http\Controllers;

use App\Exports\HistorialExport;
use App\Traits\ApiResponses; // Importamos tu Trait
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Exception;

class ExcelController extends Controller
{
    use ApiResponses; // Usamos tu Trait para mantener la consistencia

    public function exportar(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Usuario no autenticado', 401);
            }

            $tieneHistorial = $user->historialconsulta()->exists();

            if (!$tieneHistorial) {
                return $this->errorResponse('No tienes historial de consultas para exportar', 404);
            }

            if (ob_get_level() > 0) {
                ob_end_clean();
            }

            return Excel::download(
                new HistorialExport($user->id),
                'historial_consultas_' . now()->format('Ymd_His') . '.xlsx',
                \Maatwebsite\Excel\Excel::XLSX,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Access-Control-Expose-Headers' => 'Content-Disposition',
                ]
            );

        } catch (Exception $e) {
            Log::error("Error exportando Excel para usuario {$user->id}: " . $e->getMessage());
            return $this->errorResponse('Ocurrió un error al generar el reporte: ' . $e->getMessage(), 500);
        }
    }
}
