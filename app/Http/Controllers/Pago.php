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
}
