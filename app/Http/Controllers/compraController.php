<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Comprobante;
use App\Models\Producto;
use App\Models\Proveedore;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\DB;

class compraController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-compra|crear-compra|mostrar-compra|eliminar-compra'),only:['index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-compra'),only:['create','store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('mostrar-compra'),only:['show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-compra'),only:['destroy']),
        ];
    }

    public function index()
    {   
        $compras=Compra::with('comprobante','proveedore.persona')
        ->where('estado',1)
        ->latest()
        ->get();
        return view('compra.index',compact('compras'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        $proveedores=Proveedore::whereHas('persona',function($query){
            $query->where('estado', 1);
        })->get();
        $comprobantes=Comprobante::all();
        $productos=Producto::where('estado',1)->get();
        return view('compra.create',compact('proveedores','comprobantes','productos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCompraRequest $request)
    {
        try {
            DB::beginTransaction();
            //Llenar la tabla compras
            $compra=Compra::create($request->validated());
            //Llenar tabla compra_producto
            //1.- recuperar los array
            $arrayProducto_Id=$request->get('arrayidproducto');
            $arrayCantidad=$request->get('arraycantidad');
            $arrayPrecioCompra=$request->get('arrayprecioCompra');
            $arrayPrecioVenta=$request->get('arrayprecioVenta');
            //2.- realizar el llenado
            $sizeArray=count($arrayProducto_Id);
            $cont=0;
            while ($cont < $sizeArray) {
                $compra->productos()->syncWithoutDetaching([
                    $arrayProducto_Id[$cont]=>[
                        'cantidad'=>$arrayCantidad[$cont],
                        'precio_compra'=>$arrayPrecioCompra[$cont],
                        'precio_venta'=>$arrayPrecioVenta[$cont]
                    ]
                ]);
                //3.- actualizar stock
                //Buscar producto
                $producto=Producto::find($arrayProducto_Id[$cont]);
                $stockActual=$producto->stock;
                $stockNuevo=intval($arrayCantidad[$cont]);
                DB::table('productos')
                ->where('id',$producto->id)
                ->update([
                    'stock'=> $stockActual+$stockNuevo
                ]);
                $cont++;
            }

            

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
        return redirect()->route('compras.index')->with('success','Compra exitosa');
    }

    /**
     * Display the specified resource.
     */
    public function show(Compra $compra)
    {
        return view('compra.show',compact('compra'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Compra::where('id',$id)
        ->update([
            'estado'=>0
        ]);
        return redirect()->route('compras.index')->with('success','Compra eliminada');
    }
}
