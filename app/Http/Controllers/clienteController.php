<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonaRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Models\Documento;
use App\Models\Persona;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use PhpOffice\PhpSpreadsheet\IOFactory;

class clienteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-cliente|crear-cliente|editar-cliente|eliminar-cliente'), only: ['index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-cliente'), only: ['create', 'store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar-cliente'), only: ['edit', 'update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-cliente'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('importar-cliente'),only:['importarExcel','importarTxt']),
        ];
    }


    public function index()
    {
        $clientes = Cliente::with('persona.documento')->get();

        return view('cliente.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $documentos = Documento::all();

        return view('cliente.create', compact('documentos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePersonaRequest $request)
    {
        try {
            DB::beginTransaction();
            $persona = Persona::create($request->validated());
            $persona->cliente()->create([
                'persona_id' => $persona->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
        return redirect()->route('clientes.index')->with('success', 'Cliente registrado');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        $cliente->load('persona.documento');
        $documentos = Documento::all();
        return view('cliente.edit', compact('cliente', 'documentos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        try {
            DB::beginTransaction();
            Persona::where('id', $cliente->persona->id)
                ->update($request->validated());


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
        return redirect()->route('clientes.index')->with('success', 'Datos del cliente actualizados');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = '';
        $persona = Persona::find($id);
        if ($persona->estado == 1) {
            Persona::where('id', $persona->id)->update([
                'estado' => 0
            ]);
            $message = 'Cliente eliminado';
        } else {
            Persona::where('id', $persona->id)->update([
                'estado' => 1
            ]);
            $message = 'Cliente restaurado';
        }


        return redirect()->route('clientes.index')->with('success', $message);
    }





    public function importarExcel(Request $request)
    {
        $request->validate([
            'fileClientes' => 'required|file|mimes:xls,xlsx'
        ]);

        $archivo = $request->file('fileClientes');
        $spreadsheet = IOFactory::load($archivo);
        $hoja = $spreadsheet->getActiveSheet();
        $filas = $hoja->toArray();

        $insertados = 0;
        $omitidos = 0;

        // Cargamos los documentos por nombre para mapearlos a ID
        $documentos = Documento::pluck('id', 'tipo_documento')->mapWithKeys(function ($id, $tipo) {
            return [strtolower(trim($tipo)) => $id];
        });

        foreach (array_slice($filas, 1) as $fila) {
            if (empty($fila[4])) continue; // número_documento vacío

            $razon_social = trim($fila[0]);
            $direccion = trim($fila[1]);
            $tipo_persona = strtolower(trim($fila[2]));

            // Validar tipo_persona válido
            if (!in_array($tipo_persona, ['moral', 'fisica'])) {
                $omitidos++;
                continue;
            }
            $tipo_documento_nombre = strtolower(trim($fila[3])); // ej. "RUC"
            $numero_documento = trim($fila[4]);

            // Obtener ID de documento
            $documento_id = $documentos[$tipo_documento_nombre] ?? null;

            if (!$documento_id) {
                $omitidos++;
                continue; // tipo_documento no válido
            }

            // Validar duplicados
            $existe = Persona::where('numero_documento', $numero_documento)->exists();

            if ($existe) {
                $omitidos++;
                continue;
            }

            try {
                DB::beginTransaction();

                $persona = Persona::create([
                    'razon_social' => $razon_social,
                    'direccion' => $direccion,
                    'tipo_persona' => $tipo_persona,
                    'documento_id' => $documento_id,
                    'numero_documento' => $numero_documento,
                ]);

                $persona->cliente()->create([
                    'persona_id' => $persona->id
                ]);

                DB::commit();
                $insertados++;
            } catch (Exception $e) {
                DB::rollBack();
                $omitidos++;
            }
        }

        return redirect()->route('clientes.index')->with('success', "Importación completada: $insertados insertados, $omitidos duplicados o con errores.");
    }

    public function importarTxt(Request $request)
    {
        $request->validate([
            'fileClientesT' => 'required|mimes:txt|max:2048',
        ]);

        $archivo = $request->file('fileClientesT');

        $lineas = file($archivo->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $insertados = 0;
        $omitidos = 0;

        // Cargamos los documentos por nombre (igual que en Excel)
        $documentos = Documento::pluck('id', 'tipo_documento')->mapWithKeys(function ($id, $tipo) {
            return [strtolower(trim($tipo)) => $id];
        });

        foreach ($lineas as $linea) {
            $partes = explode('|', $linea);

            if (count($partes) < 5) {
                $omitidos++;
                continue;
            }

            $razon_social = trim($partes[0]);
            $direccion = trim($partes[1]);
            $tipo_persona = strtolower(trim($partes[2]));

            // Validar tipo_persona válido
            if (!in_array($tipo_persona, ['moral', 'fisica'])) {
                $omitidos++;
                continue;
            }
            $tipo_documento_nombre = strtolower(trim($partes[3])); // nombre del tipo, ej: "dni", "ruc"
            $numero_documento = trim($partes[4]);

            $documento_id = $documentos[$tipo_documento_nombre] ?? null;

            if (!$documento_id || empty($numero_documento)) {
                $omitidos++;
                continue;
            }

            // Verificamos si ya existe
            $existe = Persona::where('numero_documento', $numero_documento)->first();

            if ($existe) {
                $omitidos++;
                continue;
            }

            try {
                DB::beginTransaction();

                $persona = Persona::create([
                    'razon_social' => $razon_social,
                    'direccion' => $direccion,
                    'tipo_persona' => $tipo_persona,
                    'documento_id' => $documento_id,
                    'numero_documento' => $numero_documento,
                ]);

                $persona->cliente()->create([
                    'persona_id' => $persona->id,
                ]);

                DB::commit();
                $insertados++;
            } catch (Exception $e) {
                DB::rollBack();
                $omitidos++;
            }
        }

        return redirect()->route('clientes.index')
            ->with('success', "Importación completada: $insertados clientes registrados, $omitidos líneas omitidas.");
    }
}
