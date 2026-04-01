<?php

namespace App\Http\Controllers;

use App\Exports\HistorialExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExcelController extends Controller
{
    public function exportar(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        return Excel::download(new HistorialExport($user->id), 'users.xlsx');
    }
}
