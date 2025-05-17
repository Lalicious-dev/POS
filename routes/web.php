<?php
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\categoriaController;
use App\Http\Controllers\clienteController;
use App\Http\Controllers\compraController;
use App\Http\Controllers\homeController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\logoutController;
use App\Http\Controllers\presentacioneController;
use App\Http\Controllers\marcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\proveedoreController;
use App\Http\Controllers\roleController;
use App\Http\Controllers\userController;
use App\Http\Controllers\ventaController;
use Illuminate\Support\Facades\Route;
Route::get('/',[homeController::class,'index'])->name('panel');



Route::resources([
    'categorias' => categoriaController::class,
    'presentaciones' => presentacioneController::class,
    'marcas' => marcaController::class,
    'productos' => ProductoController::class,
    'clientes' => clienteController::class,
    'proveedores' => proveedoreController::class,
    'compras' => compraController::class,
    'ventas'=>ventaController::class,
    'users'=>userController::class,
    'roles'=>roleController::class,
    'profile'=>profileController::class
]);



Route::post('/marcas/importar',[marcaController::class,'importarExcel'])->name('marcas.importar');
Route::post('/marcas/importarTxt',[marcaController::class,'importarTxt'])->name('marcas.importartxt');
Route::post('/presentaciones/importar',[presentacioneController::class,'importarExcel'])->name('presentaciones.importar');
Route::post('/presentaciones/importarTxt',[presentacioneController::class,'importarTxt'])->name('presentaciones.importartxt');
Route::post('/categorias/importar',[categoriaController::class,'importarExcel'])->name('categorias.importar');
Route::post('/categorias/importarTxt',[categoriaController::class,'importarTxt'])->name('categorias.importartxt');
Route::post('/clientes/importar',[clienteController::class,'importarExcel'])->name('clientes.importar');
Route::post('/clientes/importarTxt',[clienteController::class,'importarTxt'])->name('clientes.importartxt');

Route::post('/proveedores/importar',[proveedoreController::class,'importarExcel'])->name('proveedores.importar');
Route::post('/proveedores/importarTxt',[proveedoreController::class,'importarTxt'])->name('proveedores.importartxt');

Route::post('/productos/importar',[ProductoController::class,'importarExcel'])->name('productos.importar');
Route::post('/productos/importarTxt',[ProductoController::class,'importarTxt'])->name('productos.importartxt');

Route::post('/chatbot', [ChatbotController::class, 'responder'])->name('chatbot.responder');

Route::get('/login', [loginController::class,'index'])->name('login');
Route::post('/login',[loginController::class,'login']);
Route::get('/logout',[logoutController::class,'logout'])->name('logout');

Route::get('/401', function () {
    return view('pages.401');
});

Route::get('/404', function () {
    return view('pages.404');
});
Route::get('/500', function () {
    return view('pages.500');
});