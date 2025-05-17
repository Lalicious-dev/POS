<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePresentacioneRequest;
use App\Http\Requests\UpdatePresentacioneRequest;
use App\Models\Caracteristica;
use App\Models\Presentacione;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class presentacioneController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-presentacione|crear-presentacione|editar-presentacione|eliminar-presentacione'), only: ['index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-presentacione'), only: ['create', 'store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar-presentacione'), only: ['edit', 'update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-presentacione'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('importar-presentacione'),only:['importarExcel','importarTxt']),
        ];
    }

    public function index()
    {
        $presentaciones = Presentacione::with('caracteristica')->latest()->get();

        return view('presentacione.index', ['presentaciones' => $presentaciones]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('presentacione.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePresentacioneRequest $request)
    {
        try {
            DB::beginTransaction();
            $caracteristica = Caracteristica::create($request->validated());
            $caracteristica->presentacione()->create([
                'caracteristica_id' => $caracteristica->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
        return redirect()->route('presentaciones.index')->with('success', 'Presentación registrada');
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
    public function edit(Presentacione $presentacione)
    {
        return view('presentacione.edit', ['presentacione' => $presentacione]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePresentacioneRequest $request, Presentacione $presentacione)
    {
        Caracteristica::where('id', $presentacione->caracteristica->id)
            ->update($request->validated());
        return redirect()->route('presentaciones.index')->with('success', 'Presentación editada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = '';
        $presentacione = Presentacione::find($id);
        if ($presentacione->caracteristica->estado == 1) {
            Caracteristica::where('id', $presentacione->caracteristica->id)->update([
                'estado' => 0
            ]);
            $message = 'Presentación eliminada';
        } else {
            Caracteristica::where('id', $presentacione->caracteristica->id)->update([
                'estado' => 1
            ]);
            $message = 'Presentación restaurada';
        }


        return redirect()->route('presentaciones.index')->with('success', $message);
    }


    public function importarExcel(Request $request)
    {
        $request->validate([
            'filePresentaciones' => 'required|file|mimes:xls,xlsx'
        ]);

        $archivo = $request->file('filePresentaciones');
        $spreadsheet = IOFactory::load($archivo);
        $hoja = $spreadsheet->getActiveSheet();
        $filas = $hoja->toArray();
        $insertados = 0;
        $omitidos = 0;
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

                    $caracteristica->presentacione()->create([
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

        return redirect()->route('presentaciones.index')->with('success', "Carga completada: $insertados insertados, $omitidos duplicados omitidos.");
    }

    public function importarTxt(Request $request)
    {
        $request->validate([
            'filePresentacionesT' => 'required|mimes:txt|max:2048',
        ]);

        $archivo = $request->file('filePresentacionesT');
        $lineas = file($archivo->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $insertados = 0;
        $omitidos = 0;

        foreach ($lineas as $linea) {
            $partes = explode('|', $linea);

            if (empty($partes[0])) {
                continue;
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

                    $caracteristica->presentacione()->create([
                        'caracteristica_id' => $caracteristica->id,
                    ]);

                    $insertados++;
                } else {
                    $omitidos++;
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $omitidos++;
            }
        }

        return redirect()->route('presentaciones.index')->with('success', "Carga completada: $insertados insertados, $omitidos duplicados omitidos.");
    }
}
