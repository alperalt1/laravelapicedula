<?php

namespace App\Exports;

use App\Models\HistorialConsulta;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class HistorialExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;
    protected $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function query()
    {
        return HistorialConsulta::query()->where('user_id', $this->id);
    }

    public function map($historial): array
    {
        return [
            $historial->cedula_consultada,
            $historial->resultado_json['genero'] ?? 'N/A', 
            $historial->resultado_json['nombre'] ?? 'N/A',
            $historial->resultado_json['conyuge'] ?? 'N/A', 
            $historial->resultado_json['profesion'] ?? 'N/A', 
            $historial->resultado_json['estadoCivil'] ?? 'N/A', 
            $historial->resultado_json['instruccion'] ?? 'N/A', 
            $historial->resultado_json['nombreMadre'] ?? 'N/A', 
            $historial->resultado_json['nombrePadre'] ?? 'N/A', 
            $historial->resultado_json['nacionalidad'] ?? 'N/A', 
            $historial->resultado_json['calleDomicilio'] ?? 'N/A', 
            $historial->resultado_json['lugarDomicilio'] ?? 'N/A', 
            $historial->resultado_json['fechaCedulacion'] ?? 'N/A', 
            $historial->resultado_json['fechaNacimiento'] ?? 'N/A', 
            $historial->resultado_json['lugarNacimiento'] ?? 'N/A', 
            $historial->resultado_json['condicionCedulado'] ?? 'N/A', 
            $historial->resultado_json['numeracionDomicilio'] ?? 'N/A', 
            $historial->resultado_json['fechaInscripcionGenero'] ?? 'N/A', 
            $historial->resultado_json['lugarInscripcionGenero'] ?? 'N/A', 
            $historial->resultado_json['fechaInscripcionDefuncion'] ?? 'N/A', 
            $historial->resultado_json['lugarNacimiento'] ?? 'N/A', 
            $historial->resultado_json['lugarNacimiento'] ?? 'N/A', 
            $historial->created_at->format('d/m/Y H:i'),
        ];
    }

    public function headings(): array
    {
        return [
            'Cédula',
            'Genero',
            'Nombre',
            'Conyuge',
            'Profesion',
            'Estado Civil',
            'Instruccion',
            'Nombre Madre',
            'Nombre Padre',
            'Nacionalidad',
            'Calle Domicilio',
            'Lugar Domicilio',
            'Fecha Cedulacion',
            'Fecha Nacimiento',
            'Lugar Nacimiento',
            'Condicion Cedulado',
            'Numeracion Domicilio',
            'Fecha Inscripcion Genero',
            'Lugar Inscripcion Genero',
            'Fecha Inscripcion Defuncion',
            'Fecha y Hora',
        ];
    }

}
