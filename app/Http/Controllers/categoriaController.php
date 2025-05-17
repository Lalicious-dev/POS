<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoriaRequest;
use App\Http\Requests\UpdateCategoriaRequest;
use App\Models\Caracteristica;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use PhpOffice\PhpSpreadsheet\IOFactory;
class categoriaController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-categoria|crear-categoria|editar-categoria|eliminar-categoria'),only:['index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-categoria'),only:['create','store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar-categoria'),only:['edit','update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-categoria'),only:['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('importar-categoria'),only:['importarExcel','importarTxt']),
        ];
    }


    public function index()
    {
        $categorias = Categoria::with('caracteristica')->latest()->get();

        return view('categoria.index', ['categorias' => $categorias]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('categoria.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoriaRequest $request)
    {
        //dd($request);
        try {
            DB::beginTransaction();
            $caracteristica = Caracteristica::create($request->validated());
            $caracteristica->categoria()->create([
                'caracteristica_id' => $caracteristica->id
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
        return redirect()->route('categorias.index')->with('success', 'Categoría registrada');
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
    public function edit(Categoria $categoria)
    {

        return view('categoria.edit', ['categoria' => $categoria]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoriaRequest $request, Categoria $categoria)
    {
        Caracteristica::where('id', $categoria->caracteristica->id)
            ->update($request->validated());
        return redirect()->route('categorias.index')->with('success', 'Categoría editada');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = '';
        $categoria = Categoria::find($id);
        if ($categoria->caracteristica->estado == 1) {
            Caracteristica::where('id', $categoria->caracteristica->id)->update([
                'estado' => 0
            ]);
            $message = 'Categoría eliminada';
        } else {
            Caracteristica::where('id', $categoria->caracteristica->id)->update([
                'estado' => 1
            ]);
            $message = 'Categoría restaurada';
        }


        return redirect()->route('categorias.index')->with('success', $message);
    }



    public function importarExcel(Request $request)
    {
        $request->validate([
            'fileCategorias' => 'required|file|mimes:xls,xlsx'
        ]);

        $archivo = $request->file('fileCategorias');
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

                    $caracteristica->categoria()->create([
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

        return redirect()->route('categorias.index')->with('success', "Carga completada: $insertados insertados, $omitidos duplicados omitidos.");
    }

    public function importarTxt(Request $request)
    {
        $request->validate([
            'fileCategoriasT' => 'required|mimes:txt|max:2048', // máximo 2MB
        ]);

        $archivo = $request->file('fileCategoriasT');

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

                    $caracteristica->categoria()->create([
                        'caracteristica_id' => $caracteristica->id,
                    ]);

                    $insertados++;
                } else {
                    $omitidos++;
                }

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
            }
        }

        return redirect()->route('categorias.index')->with('success', "Carga completada: $insertados insertados, $omitidos duplicados omitidos.");
    }
}
