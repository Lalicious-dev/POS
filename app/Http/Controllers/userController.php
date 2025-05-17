<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorageUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class userController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('ver-user|crear-user|editar-user|eliminar-user'),only:['index']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('crear-user'),only:['create','store']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('editar-user'),only:['edit','update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('eliminar-user'),only:['destroy']),
        ];
    }
    public function index()
    {
        $users=User::all();
        return view('user.index',compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles=Role::all();
        return view('user.create',compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorageUserRequest $request)
    {
        try {
            DB::beginTransaction();
            //encriptar contraseña
            $fieldHash=Hash::make($request->password);
            //modificar el valor de password en nuestro request
            $request->merge(['password'=>$fieldHash]);
            //Crear usuario
            $user=User::create($request->all());

            //Asignar rol
            $user->assignRole($request->role);


            DB::commit();
        } catch (Exception $e) {
            
            DB::rollBack();
        }
        return redirect()->route('users.index')->with('success','Usuario registrado');
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
    public function edit(User $user)
    {
        /**
         *  try {
            DB::beginTransaction();
                
            DB::commit();
        } catch (Exception $e) {
            
            DB::rollBack();
        }
        */
        $roles=Role::all();
        return view('user.edit',compact('user','roles'));
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request,User $user)
    {
        try {
            DB::beginTransaction();
            
            //Comprobar password y aplicar hash
            if (empty($request->password)) {
                $request=Arr::except($request,array('password'));
            } else {
                $fieldHash=Hash::make($request->password);
                $request->merge(['password'=>$fieldHash]);
            }

            $user->update($request->all());
            //Actualizar rol

            $user->syncRoles([$request->role]);


            DB::commit();
        } catch (Exception $e) {
            
            DB::rollBack();
        }
        return redirect()->route('users.index')->with('success','Usuario editado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {   
        $user=User::find($id);
        //Eliminar rol
        $rolUser=$user->getRoleNames()->first();
        $user->removeRole($rolUser);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuario eliminado');
    }
}
