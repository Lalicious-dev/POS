<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\Presentacione;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log; // Asegúrate de importar esto arriba
use Illuminate\Support\Str;

class ProductoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-producto|crear-producto|editar-producto|eliminar-producto'), only: ['index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-producto'), only: ['create', 'store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar-producto'), only: ['edit', 'update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-producto'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('importar-producto'),only:['importarExcel','importarTxt']),
        ];
    }

    public function index()
    {
        $productos = Producto::with(['categorias.caracteristica', 'marca.caracteristica', 'presentacione.caracteristica'])
            ->latest()
            ->get();

        return view('producto.index', compact('productos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->select('marcas.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();
        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->select('presentaciones.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();
        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->select('categorias.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();
        return view('producto.create', compact('marcas', 'presentaciones', 'categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductoRequest $request)
    {
        try {
            DB::beginTransaction();
            //Tabla productos
            $producto = new Producto();
            if ($request->hasFile('img_path')) {
                $name = $producto->handleUploadImage($request->File('img_path'));
            } else {
                $name = null;
            }

            $producto->fill([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'img_path' => $name,
                'marca_id' => $request->marca_id,
                'presentacione_id' => $request->presentacione_id,
            ]);
            $producto->save();
            //Tabla categoria producto
            $categorias = $request->get('categorias');
            $producto->categorias()->attach($categorias);



            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
        return redirect()->route('productos.index')->with('success', 'Producto registrado');
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
    public function edit(Producto $producto)
    {
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->select('marcas.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();
        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->select('presentaciones.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();
        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->select('categorias.id as id', 'c.nombre as nombre')
            ->where('c.estado', 1)
            ->get();
        return view('producto.edit', compact('producto', 'marcas', 'presentaciones', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductoRequest $request, Producto $producto)
    {
        try {
            DB::beginTransaction();
            //Tabla productos
            if ($request->hasFile('img_path')) {
                $name = $producto->handleUploadImage($request->File('img_path'));

                //Eliminar si existe una imagen antigua
                if (Storage::disk('public')->exists('productos/' . $producto->img_path)) {
                    Storage::disk('public')->delete('productos/' . $producto->img_path);
                }
            } else {
                $name = $producto->img_path;
            }

            $producto->fill([
                'codigo' => $request->codigo,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'fecha_vencimiento' => $request->fecha_vencimiento,
                'img_path' => $name,
                'marca_id' => $request->marca_id,
                'presentacione_id' => $request->presentacione_id,
            ]);
            $producto->save();
            //Tabla categoria producto
            $categorias = $request->get('categorias');
            $producto->categorias()->sync($categorias);



            DB::commit();
        } catch (Exception $e) {

            DB::rollBack();
        }

        return redirect()->route('productos.index')->with('success', 'Producto editado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $message = '';
        $producto = Producto::find($id);
        if ($producto->estado == 1) {
            Producto::where('id', $producto->id)->update([
                'estado' => 0
            ]);
            $message = 'Producto eliminado';
        } else {
            Producto::where('id', $producto->id)->update([
                'estado' => 1
            ]);
            $message = 'Producto restaurado';
        }


        return redirect()->route('productos.index')->with('success', $message);
    }





    public function importarExcel(Request $request)
    {
        $request->validate([
            'fileProductos' => 'required|file|mimes:xls,xlsx'
        ]);

        $archivo = $request->file('fileProductos');
        $spreadsheet = IOFactory::load($archivo);
        $hoja = $spreadsheet->getActiveSheet();
        $filas = $hoja->toArray();

        $insertados = 0;
        $omitidos = 0;

        // Cargamos las Marcas por nombre desde la tabla caracteristicas
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->pluck('marcas.id', 'c.nombre')
            ->mapWithKeys(function ($id, $nombre) {
                return [strtolower(trim($nombre)) => $id];
            });

        // Cargamos las Presentaciones por nombre desde la tabla caracteristicas
        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->pluck('presentaciones.id', 'c.nombre')
            ->mapWithKeys(function ($id, $nombre) {
                return [strtolower(trim($nombre)) => $id];
            });

        // Cargamos las Categorías por nombre desde la tabla caracteristicas
        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->pluck('categorias.id', 'c.nombre')
            ->mapWithKeys(function ($id, $nombre) {
                return [strtolower(trim($nombre)) => $id];
            });

        foreach (array_slice($filas, 1) as $fila) {
            if (empty($fila[0]) || empty($fila[1]) || empty($fila[4]) || empty($fila[5]) || empty($fila[6])) {
                $omitidos++;
                continue; // Campos obligatorios vacíos
            }

            $codigo = trim($fila[0]);
            $nombre = trim($fila[1]);
            $descripcion = trim($fila[2]);
            $fecha_vencimiento = !empty($fila[3]) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fila[3])->format('Y-m-d') : null;
            $marca_nombre = strtolower(trim($fila[4]));
            $presentacion_nombre = strtolower(trim($fila[5]));
            $categorias_nombres = array_map('trim', explode(',', strtolower($fila[6])));
            $img_path = trim($fila[7]);

            // Obtener IDs de Marca y Presentación
            $marca_id = $marcas[$marca_nombre] ?? null;
            $presentacione_id = $presentaciones[$presentacion_nombre] ?? null;

            if (!$marca_id || !$presentacione_id) {
                $omitidos++;
                continue; // Marca o Presentación no válida
            }

            // Obtener IDs de Categorías
            $categoria_ids = [];
            foreach ($categorias_nombres as $categoria_nombre) {
                if (isset($categorias[$categoria_nombre])) {
                    $categoria_ids[] = $categorias[$categoria_nombre];
                } else {
                    $omitidos++;
                    continue 2; // Si alguna categoría no existe, omitir el producto completo
                }
            }

            // Validar duplicados por código
            $existe = Producto::where('codigo', $codigo)->exists();
            if ($existe) {
                $omitidos++;
                continue;
            }

            try {
                DB::beginTransaction();

                $producto = Producto::create([
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'fecha_vencimiento' => $fecha_vencimiento,
                    'marca_id' => $marca_id,
                    'presentacione_id' => $presentacione_id,
                    'img_path' => $img_path, // Asumiendo que la ruta es válida si se proporciona
                ]);

                // Asignar categorías al producto
                $producto->categorias()->attach($categoria_ids);

                DB::commit();
                $insertados++;
            } catch (Exception $e) {
                DB::rollBack();
                $omitidos++;
                // Puedes agregar aquí un log del error si lo necesitas: Log::error("Error al importar producto: " . $e->getMessage());
            }
        }

        return redirect()->route('productos.index')->with('success', "Importación de productos completada: $insertados insertados, $omitidos duplicados o con errores.");
    }




    public function importarTxt(Request $request)
    {
        $request->validate([
            'fileProductosT' => 'required|mimes:txt|max:2048',
        ]);

        $archivo = $request->file('fileProductosT');
        $lineas = file($archivo->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $insertados = 0;
        $omitidos = 0;

        // Cargamos las Marcas por nombre desde la tabla caracteristicas
        $marcas = Marca::join('caracteristicas as c', 'marcas.caracteristica_id', '=', 'c.id')
            ->pluck('marcas.id', 'c.nombre')
            ->mapWithKeys(function ($id, $nombre) {
                return [strtolower(trim($nombre)) => $id];
            });

        // Cargamos las Presentaciones por nombre desde la tabla caracteristicas
        $presentaciones = Presentacione::join('caracteristicas as c', 'presentaciones.caracteristica_id', '=', 'c.id')
            ->pluck('presentaciones.id', 'c.nombre')
            ->mapWithKeys(function ($id, $nombre) {
                return [strtolower(trim($nombre)) => $id];
            });

        // Cargamos las Categorías por nombre desde la tabla caracteristicas
        $categorias = Categoria::join('caracteristicas as c', 'categorias.caracteristica_id', '=', 'c.id')
            ->pluck('categorias.id', 'c.nombre')
            ->mapWithKeys(function ($id, $nombre) {
                return [strtolower(trim($nombre)) => $id];
            });

        foreach ($lineas as $linea) {
            $partes = explode('|', $linea);

            if (count($partes) < 7) { // Ajustamos la cantidad de partes esperadas
                $omitidos++;
                continue; // Línea incompleta
            }

            $codigo = trim($partes[0]);
            $nombre = trim($partes[1]);
            $descripcion = trim($partes[2]);
            $fecha_vencimiento = trim($partes[3]) ?: null; // Si está vacío, se guarda como null
            $marca_nombre = strtolower(trim($partes[4]));
            $presentacion_nombre = strtolower(trim($partes[5]));
            $categorias_nombres = array_map('trim', explode(',', strtolower($partes[6])));
            $img_path = trim($partes[7]) ?? null; // Si no hay octava parte, o está vacía, será null

            // Obtener IDs de Marca y Presentación
            $marca_id = $marcas[$marca_nombre] ?? null;
            $presentacione_id = $presentaciones[$presentacion_nombre] ?? null;

            if (!$marca_id || !$presentacione_id) {
                $omitidos++;
                continue; // Marca o Presentación no válida
            }

            // Obtener IDs de Categorías
            $categoria_ids = [];
            foreach ($categorias_nombres as $categoria_nombre) {
                if (isset($categorias[$categoria_nombre])) {
                    $categoria_ids[] = $categorias[$categoria_nombre];
                } else {
                    $omitidos++;
                    continue 2; // Si alguna categoría no existe, omitir el producto completo
                }
            }

            // Validar duplicados por código
            $existe = Producto::where('codigo', $codigo)->exists();
            if ($existe) {
                $omitidos++;
                continue;
            }

            try {
                DB::beginTransaction();

                $producto = Producto::create([
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'fecha_vencimiento' => $fecha_vencimiento,
                    'marca_id' => $marca_id,
                    'presentacione_id' => $presentacione_id,
                    'img_path' => $img_path,
                ]);

                // Asignar categorías al producto
                $producto->categorias()->attach($categoria_ids);

                DB::commit();
                $insertados++;
            } catch (Exception $e) {
                DB::rollBack();
                $omitidos++;
                dd($e);
                // Puedes agregar aquí un log del error si lo necesitas: Log::error("Error al importar producto desde TXT: " . $e->getMessage());
            }
        }

        return redirect()->route('productos.index')
            ->with('success', "Importación de productos desde TXT completada: $insertados insertados, $omitidos líneas omitidas o con errores.");
    }
}
