<?php

namespace App\Http\Controllers;

use App\Mail\RecuperarPasswordMail;
use App\Mail\WelcomeMail;
use App\Models\Plan;
use App\Models\User;
use App\Traits\ApiResponses;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use ApiResponses;
    public function registrar(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed']
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            $planBasico = Plan::where('name', 'Plan Básico')->first();

            $user->suscripcion()->create([
                'plan_id' => $planBasico->id,
                'consultas_disponibles' => $planBasico->limit_consultas,
                'fecha_inicio' => now(),
                'fecha_vencimiento' => now()->addDays($planBasico->duration_days),
                'is_active' => true
            ]);

            return $user;
        });

        try {
            Mail::to($user->email)->queue(new WelcomeMail($user));
        } catch (Exception $e) {
            Log::error("Error enviando correo a {$user->email}: " . $e->getMessage());
        }


        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 'Usuario Registrado Exitosamente', 201);
    }

    public function iniciarsesion(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credenciales)) {
            return $this->errorResponse('Credenciales invalidas', 401);
        }

        $user = Auth::user();

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'Inicio de Sesión Exito', 200);
    }

    public function enviarcorreocontrasena(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $codigo = rand(100000, 999999);
            Mail::to($user->email)->send(new RecuperarPasswordMail($codigo));
        }

        return response()->json([
            'message' => 'Si el correo existe, el código ha sido enviado.'
        ]);
    }

    // public function recuperarcontrasena(Request $request)
    // {
    //     $credenciales = $request->validate([
    //         'email'
    //     ])
    // }
}
