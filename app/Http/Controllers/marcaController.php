<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMarcaRequest;
use App\Http\Requests\UpdateMarcaRequest;
use App\Models\Caracteristica;
use App\Models\Marca;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use PhpOffice\PhpSpreadsheet\IOFactory;

class marcaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-marca|crear-marca|editar-marca|eliminar-marca'), only: ['index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-marca'), only: ['create', 'store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar-marca'), only: ['edit', 'update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-marca'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('importar-marca'),only:['importarExcel','importarTxt']),
        ];
    }

    public function index()
    {
        $marcas = Marca::with('caracteristica')->latest()->get();

        return view('marca.index', ['marcas' => $marcas]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('marca.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMarcaRequest $request)
    {
        //
        try {
            DB::beginTransaction();
            $caracteristica = Caracteristica::create($request->validated());
            $caracteristica->marca()->create([
                'caracteristica_id' => $caracteristica->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
        return redirect()->route('marcas.index')->with('success', 'Marca registrada');
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
    public function edit(Marca $marca)
    {
        return view('marca.edit', ['marca' => $marca]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMarcaRequest $request, Marca $marca)
    {
        Caracteristica::where('id', $marca->caracteristica->id)
            ->update($request->validated());
        return redirect()->route('marcas.index')->with('success', 'Marca editada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = '';
        $marca = Marca::find($id);
        if ($marca->caracteristica->estado == 1) {
            Caracteristica::where('id', $marca->caracteristica->id)->update([
                'estado' => 0
            ]);
            $message = 'Marca eliminada';
        } else {
            Caracteristica::where('id', $marca->caracteristica->id)->update([
                'estado' => 1
            ]);
            $message = 'Marca restaurada';
        }


        return redirect()->route('marcas.index')->with('success', $message);
    }

    public function importarExcel(Request $request)
    {
        $request->validate([
            'fileMarcas' => 'required|file|mimes:xls,xlsx'
        ]);

        $archivo = $request->file('fileMarcas');
        $spreadsheet = IOFactory::load($archivo);
        $hoja = $spreadsheet->getActiveSheet();
        $filas = $hoja->toArray();
        $insertados = 0;
        $omitidos = 0;
        // Asumimos la primera fila como encabezados
        foreach (array_slice($filas, 1) as $fila) {
            if (empty($fila[0])) {
                continue;
            }

            try {
                DB::beginTransaction();

                // Verifica si ya existe una característica con ese nombre
                $caracteristicaExistente = Caracteristica::where('nombre', $fila[0])->first();

                if (!$caracteristicaExistente) {
                    // Solo si no existe, la crea
                    $caracteristica = Caracteristica::create([
                        'nombre' => $fila[0],
                        'descripcion' => $fila[1],
                    ]);

                    $caracteristica->marca()->create([
                        'caracteristica_id' => $caracteristica->id
                    ]);
                    $insertados++;
                }else{
                    $omitidos++;
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
            }
        }

        return redirect()->route('marcas.index')->with('success', "Carga completada: $insertados insertados, $omitidos duplicados omitidos.");
    }

    public function importarTxt(Request $request)
    {
        $request->validate([
            'fileMarcasT' => 'required|mimes:txt|max:2048', // máximo 2MB
        ]);

        $archivo = $request->file('fileMarcasT');

        // Abre el archivo para lectura
        $lineas = file($archivo->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $insertados = 0;
        $omitidos = 0;
        foreach ($lineas as $linea) {
            // Suponiendo que el TXT tiene el formato: nombre|descripcion
            $partes = explode('|', $linea);

            // Validamos que tenga al menos nombre
            if (empty($partes[0])) {
                continue; // omitimos esta línea si no tiene nombre
            }
            $nombre = trim($partes[0]);
            $descripcion = isset($partes[1]) ? trim($partes[1]) : null;

            try {
                DB::beginTransaction();

                $caracteristicaExistente = Caracteristica::where('nombre', $nombre)->first();

                if (!$caracteristicaExistente) {
                    $caracteristica = Caracteristica::create([
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                    ]);

                    $caracteristica->marca()->create([
                        'caracteristica_id' => $caracteristica->id,
                    ]);

                    $insertados++;
                } else {
                    $omitidos++;
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                // Aquí podrías guardar el error en un log si deseas
            }
        }

        return redirect()->route('marcas.index')->with('success', "Carga completada: $insertados insertados, $omitidos duplicados omitidos.");
    }
}
