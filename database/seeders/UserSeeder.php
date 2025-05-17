<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //primer usuario insertado
        /* User::insert([
            [
                'name'=>'Eduardo',
                'email'=>'lalito.es37@gmail.com',
                'password'=>bcrypt('edu128')
            ]
        ]);
        
        */
        $user= User::create([
            [
                'name'=>'Eduardo',
                'email'=>'lalito.es37@gmail.com',
                'password'=>bcrypt('12345678')
            ]
        ]);
        //Usuario administrador
        $rol=Role::create(['name'=>'administrador']);
        $permisos=Permission::pluck('id','id')->all();
        $rol->syncPermissions($permisos);
        //$user=User::find(1);
        $user->assignRole('administrador');
    }
}
