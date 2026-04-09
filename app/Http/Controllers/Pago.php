<?php

namespace App\Http\Controllers;

use App\Models\CompraPlan;
use App\Models\Plan;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Pago extends Controller
{
    use ApiResponses;
    public function generarOrden(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = $request->user();
        $plan = Plan::find($validated['plan_id']);

        $baseImponible = (int) ($plan->price * 100);
        $iva = (int) ($baseImponible * 0.15);
        $total = $baseImponible + $iva;

        $transactionId = 'TRANS-' . time() . '-' . $user->id;

        try {
            $response = Http::withToken(env('PAYPHONE_TOKEN'))
                ->acceptJson()
                ->post('https://pay.payphonetodoesposible.com/api/Links', [
                    "amount" => $total,
                    "amountWithTax" => $baseImponible,
                    "tax" => $iva,
                    "clientTransactionId" => $transactionId,
                    "currency" => "USD",
                    "storeId" => env('PAYPHONE_STORE_ID'),
                    "reference" => "Compra de " . $plan->name,
                    "expireIn" => 10
                ]);

            if ($response->failed()) {
                return $this->errorResponse('Error al conectar con Payphone: ' . $response->body(), 502);
            }

            $rawBody = trim($response->body(), '"');
            $payphoneData = $response->json();

            $redirectUrl = null;

            if (filter_var($rawBody, FILTER_VALIDATE_URL)) {
                $redirectUrl = $rawBody;
            } elseif (is_array($payphoneData) && isset($payphoneData['redirectUrl'])) {
                $redirectUrl = $payphoneData['redirectUrl'];
            }

            if (!$redirectUrl) {
                return $this->errorResponse(
                    'No se pudo obtener el link de pago',
                    502,
                    ['respuesta_recibida' => $response->body()]
                );
            }

            CompraPlan::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'monto' => $total / 100,
                'metodo_pago' => 'Payphone',
                'referencia_pago' => $transactionId,
                'estado' => 'pendiente',
                'consultas_adquiridas' => $plan->limit_consultas
            ]);

            return $this->successResponse([
                'redirectUrl' => $redirectUrl,
                'transactionId' => $transactionId
            ], 'Link de pago generado');
        } catch (\Exception $e) {
            Log::error($e->getMessage());


            return $this->errorResponse(
                'Ocurrió un error interno en el servidor',
                500,
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function payphoneWebhook(Request $request)
    {
        $transactionId = $request->clientTransactionId;
        $compra = CompraPlan::where('referencia_pago', $transactionId)->first();

        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada'], 404);
        }

        if ($request->transactionStatus == 3) {
            $compra->update(['estado' => 'completado']);
            $user = $compra->user;
            $suscripcion = $user->suscripcion;
            $suscripcion->increment('consultas_disponibles', $compra->consultas_adquiridas);

            return response()->json(['message' => 'Pago procesado y consultas acreditadas'], 200);
        }

        return response()->json(['message' => 'Pago no aprobado'], 400);
    }

    public function validartransaccion($transactionId)
    {
        $compra = CompraPlan::where('referencia_pago', $transactionId)
            ->where('estado', 'pendiente')
            ->first();

        if (!$compra) {
            return response()->json(['message' => 'Compra no encontrada o ya procesada'], 404);
        }
        try {
            $response = Http::withToken(env('PAYPHONE_TOKEN'))
                ->get("https://pay.payphonetodoesposible.com/api/Sale/client/{$transactionId}");

            if ($response->successful()) {
                $dataArray = $response->json();
                if (!empty($dataArray) && is_array($dataArray)) {
                    $data = $dataArray[0];
                    if ($data['statusCode'] == 3) {
                        $compra->update(['estado' => 'completado']);
                        $user = $compra->user;
                        $suscripcion = $user->suscripcion;
                        $suscripcion->update([
                            'plan_id' => $compra->plan_id, 
                            'consultas_disponibles' => $suscripcion->consultas_disponibles + $compra->consultas_adquiridas,
                            'is_active' => true
                        ]);
                        return response()->json([
                            'status' => 'success',
                            'message' => '¡Pago verificado! Consultas acreditadas.',
                            'consultas' => $suscripcion->load('plan')
                        ], 200);
                    }
                }
            }

            return response()->json(['message' => 'El pago aún no aparece como aprobado en Payphone.'], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error de conexión con la pasarela'], 500);
        }
    }

    public function historialpago(Request $request)
    {
        $user = $request->user();
        $pagos = CompraPlan::where('user_id', $user->id)
            ->with(['plan:id,name'])
            ->select('id', 'plan_id', 'monto', 'referencia_pago', 'estado', 'created_at')
            ->latest()->get();

        if ($pagos->isEmpty()) {
            return $this->errorResponse('No se encontraron registros de pago', 404);
        }

        return $this->successResponse($pagos, 'Historial de pagos recuperado', 200);
    }
}
