<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class roleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-role|crear-role|editar-role|eliminar-role'),only:['index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-role'),only:['create','store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar-role'),only:['edit','update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-role'),only:['destroy']),
        
        ];
    }

    public function index()
    {
        $roles = Role::all();
        return view('role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permisos = Permission::all();
        return view('role.create', compact('permisos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permission' => 'required'
        ]);

        try {
            DB::beginTransaction();
            //Crear rol
            $rol = Role::create(['name' => $request->name]);

            //Asignar permisos
            $rol->syncPermissions($request->permission);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }


        return redirect()->route('roles.index')->with('success', 'Rol registrado');
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
    public function edit(Role $role)
    {
        $permisos = Permission::all();
        return view('role.edit', compact('role', 'permisos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permission' => 'required'
        ]);

        try {
            DB::beginTransaction();

            //Actualizar rol
            Role::where('id', $role->id)
                ->update([
                    'name' => $request->name
                ]);

            //Actualizar permisos
            $role->syncPermissions($request->permission);

            DB::commit();
        } catch (Exception $e) {
            
            DB::rollBack();
        }

        return redirect()->route('roles.index')->with('success', 'Rol editado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Role::where('id',$id)->delete();
        return redirect()->route('roles.index')->with('success', 'Rol eliminado');
    }
}
