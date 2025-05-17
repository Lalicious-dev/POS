<?php

namespace Database\Seeders;

use App\Models\Chatbot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChatbotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*Chatbot::insert([
            [
                'pregunta'=>'quien te entreno',
                'respuesta'=>'El responsable de mi entrenamiento fue Eduardo Salinas Pavón'
            ],
        ]);*/
        Chatbot::insert([
            ['pregunta' => 'quien eres', 'respuesta' => 'Un asistente virtual para ayudarte en pequeñas dudas.'],
            ['pregunta' => 'que modulos tengo disponibles', 'respuesta' => 'Los módulos con los cuales puedes interactuar son los asignados por el administrador y se ven reflejados en la barra lateral izquierda de tu pantalla, algunos pueden contener otros submódulos.'],
            ['pregunta' => 'que funciones puedo usar', 'respuesta' => 'Los módulos con los cuales puedes interactuar son los asignados por el administrador y se ven reflejados en la barra lateral izquierda de tu pantalla, algunos pueden contener otros submódulos.'],
            ['pregunta' => 'que herramientas puedo utilizar', 'respuesta' => 'Los módulos con los cuales puedes interactuar son los asignados por el administrador y se ven reflejados en la barra lateral izquierda de tu pantalla, algunos pueden contener otros submódulos.'],
            ['pregunta' => 'cuales son los módulos disponibles', 'respuesta' => 'Los módulos con los cuales puedes interactuar son los asignados por el administrador y se ven reflejados en la barra lateral izquierda de tu pantalla, algunos pueden contener otros submódulos.'],
            ['pregunta' => 'que puedo hacer desde mi cuenta', 'respuesta' => 'Los módulos con los cuales puedes interactuar son los asignados por el administrador y se ven reflejados en la barra lateral izquierda de tu pantalla, algunos pueden contener otros submódulos.'],
            ['pregunta' => 'que opciones hay en la barra lateral', 'respuesta' => 'Los módulos con los cuales puedes interactuar son los asignados por el administrador y se ven reflejados en la barra lateral izquierda de tu pantalla, algunos pueden contener otros submódulos.'],

            ['pregunta' => 'como vuelvo a la pagina anterior', 'respuesta' => 'Puedes presionar click en la barra del navegador en regresar.'],
            ['pregunta' => 'como regreso si no tengo acceso', 'respuesta' => 'Puedes presionar click en la barra del navegador en regresar.'],
            ['pregunta' => 'no tengo permiso, como vuelvo atras', 'respuesta' => 'Puedes presionar click en la barra del navegador en regresar.'],
            ['pregunta' => 'como volver si me sale acceso denegado', 'respuesta' => 'Puedes presionar click en la barra del navegador en regresar.'],
            ['pregunta' => 'como puedo ir al inicio si me bloquea el acceso', 'respuesta' => 'Puedes presionar click en la barra del navegador en regresar.'],

            ['pregunta' => 'que modulos se relacionan entre si', 'respuesta' => 'Dentro del sistema hay varios modulos relacionados: como categorias, presentaciones y marcas hacia productos, el módulo de personas incluye a clientes y proveedores, mientras que compras y ventas relacionan a los demás mencionados.'],
            ['pregunta' => 'hay modulos conectados o relacionados', 'respuesta' => 'Dentro del sistema hay varios modulos relacionados: como categorias, presentaciones y marcas hacia productos, el módulo de personas incluye a clientes y proveedores, mientras que compras y ventas relacionan a los demás mencionados.'],
            ['pregunta' => 'que modulos tienen relacion', 'respuesta' => 'Dentro del sistema hay varios modulos relacionados: como categorias, presentaciones y marcas hacia productos, el módulo de personas incluye a clientes y proveedores, mientras que compras y ventas relacionan a los demás mencionados.'],
            ['pregunta' => 'que modulos dependen de otros', 'respuesta' => 'Dentro del sistema hay varios modulos relacionados: como categorias, presentaciones y marcas hacia productos, el módulo de personas incluye a clientes y proveedores, mientras que compras y ventas relacionan a los demás mencionados.'],
            ['pregunta' => 'cuales estan vinculados entre si', 'respuesta' => 'Dentro del sistema hay varios modulos relacionados: como categorias, presentaciones y marcas hacia productos, el módulo de personas incluye a clientes y proveedores, mientras que compras y ventas relacionan a los demás mencionados.'],

            ['pregunta' => 'no veo algunas secciones', 'respuesta' => 'Tal vez porque no cuentas con los permisos para acceder a ellas.'],
            ['pregunta' => 'por que no aparecen algunas partes del sistema', 'respuesta' => 'Tal vez porque no cuentas con los permisos para acceder a ellas.'],
            ['pregunta' => 'hay paginas que no puedo abrir', 'respuesta' => 'Tal vez porque no cuentas con los permisos para acceder a ellas.'],
            ['pregunta' => 'algunas paginas no cargan', 'respuesta' => 'Tal vez porque no cuentas con los permisos para acceder a ellas.'],
            ['pregunta' => 'algunas secciones no se muestran', 'respuesta' => 'Tal vez porque no cuentas con los permisos para acceder a ellas.'],

            ['pregunta' => 'que hago si no tengo permiso', 'respuesta' => 'Solicite al administrador un cambio de ellos en caso de ser necesario.'],
            ['pregunta' => 'como obtener permisos', 'respuesta' => 'Solicite al administrador un cambio de ellos en caso de ser necesario.'],
            ['pregunta' => 'no tengo acceso, que hago', 'respuesta' => 'Solicite al administrador un cambio de ellos en caso de ser necesario.'],
            ['pregunta' => 'si me falta permiso, como lo consigo', 'respuesta' => 'Solicite al administrador un cambio de ellos en caso de ser necesario.'],
            ['pregunta' => 'como solicitar acceso', 'respuesta' => 'Solicite al administrador un cambio de ellos en caso de ser necesario.'],

            ['pregunta' => 'me pueden dar el formato para Excel o Txt', 'respuesta' => 'Por supuesto que si, te lo proporcionará el administrador.'],
            ['pregunta' => 'donde consigo el formato de importacion', 'respuesta' => 'Por supuesto que si, te lo proporcionará el administrador.'],
            ['pregunta' => 'me falta el archivo de ejemplo para importar', 'respuesta' => 'Por supuesto que si, te lo proporcionará el administrador.'],
            ['pregunta' => 'necesito el formato para cargar datos', 'respuesta' => 'Por supuesto que si, te lo proporcionará el administrador.'],
            ['pregunta' => 'me pueden enviar el formato para subir archivos', 'respuesta' => 'Por supuesto que si, te lo proporcionará el administrador.'],
            ['pregunta' => 'como cambio mi contraseña', 'respuesta' => 'Si, dirígete hacia el icono de usuario en la parte superior derecha y da click en configuración, ahí podrás hacer los cambios pertinentemente.'],
            ['pregunta' => 'puedo modificar mi clave', 'respuesta' => 'Si, dirígete hacia el icono de usuario en la parte superior derecha y da click en configuración, ahí podrás hacer los cambios pertinentemente.'],
            ['pregunta' => 'donde cambio mi contraseña', 'respuesta' => 'Si, dirígete hacia el icono de usuario en la parte superior derecha y da click en configuración, ahí podrás hacer los cambios pertinentemente.'],
            ['pregunta' => 'hay forma de actualizar mi contraseña', 'respuesta' => 'Si, dirígete hacia el icono de usuario en la parte superior derecha y da click en configuración, ahí podrás hacer los cambios pertinentemente.'],
            ['pregunta' => 'es posible cambiar la contraseña', 'respuesta' => 'Si, dirígete hacia el icono de usuario en la parte superior derecha y da click en configuración, ahí podrás hacer los cambios pertinentemente.'],

            ['pregunta' => 'puedo editar el tipo de cliente', 'respuesta' => 'No, no es posible esa acción. Pero si te has equivado consulta al administrador.'],
            ['pregunta' => 'se puede cambiar el tipo de cliente', 'respuesta' => 'No, no es posible esa acción. Pero si te has equivado consulta al administrador.'],
            ['pregunta' => 'como modificar el tipo de cliente', 'respuesta' => 'No, no es posible esa acción. Pero si te has equivado consulta al administrador.'],
            ['pregunta' => 'me equivoque de tipo de cliente', 'respuesta' => 'No, no es posible esa acción. Pero si te has equivado consulta al administrador.'],
            ['pregunta' => 'me deje el tipo de cliente incorrecto', 'respuesta' => 'No, no es posible esa acción. Pero si te has equivado consulta al administrador.'],

            ['pregunta' => 'que hago si el cliente no tiene documento', 'respuesta' => 'Solicitalo en tiempo hábil y realiza el registro después, el documento es necesario para el manejo de la información.'],
            ['pregunta' => 'puedo registrar un cliente sin documento', 'respuesta' => 'Solicitalo en tiempo hábil y realiza el registro después, el documento es necesario para el manejo de la información.'],
            ['pregunta' => 'que pasa si falta un documento del cliente', 'respuesta' => 'Solicitalo en tiempo hábil y realiza el registro después, el documento es necesario para el manejo de la información.'],
            ['pregunta' => 'no tengo el documento del cliente, que hago', 'respuesta' => 'Solicitalo en tiempo hábil y realiza el registro después, el documento es necesario para el manejo de la información.'],
            ['pregunta' => 'puedo omitir el documento del cliente', 'respuesta' => 'Solicitalo en tiempo hábil y realiza el registro después, el documento es necesario para el manejo de la información.'],

            ['pregunta' => 'no encuentro un producto', 'respuesta' => 'Significa que el producto ha sido eliminado de la base de datos, o simplemente búscalo en el buscador de la tabla, corrobora la información.'],
            ['pregunta' => 'donde esta el producto que necesito', 'respuesta' => 'Significa que el producto ha sido eliminado de la base de datos, o simplemente búscalo en el buscador de la tabla, corrobora la información.'],
            ['pregunta' => 'por que no aparece un producto', 'respuesta' => 'Significa que el producto ha sido eliminado de la base de datos, o simplemente búscalo en el buscador de la tabla, corrobora la información.'],
            ['pregunta' => 'no veo un producto que antes estaba', 'respuesta' => 'Significa que el producto ha sido eliminado de la base de datos, o simplemente búscalo en el buscador de la tabla, corrobora la información.'],
            ['pregunta' => 'un producto desaparecio de la tabla', 'respuesta' => 'Significa que el producto ha sido eliminado de la base de datos, o simplemente búscalo en el buscador de la tabla, corrobora la información.'],

            ['pregunta' => 'por que no veo el precio del producto', 'respuesta' => 'Porque el precio puede cambiar en base a las compras hacia el proveedor (puede ser cambiante), solo ves reflejado el precio al realizar la venta.'],
            ['pregunta' => 'el precio del producto no se muestra', 'respuesta' => 'Porque el precio puede cambiar en base a las compras hacia el proveedor (puede ser cambiante), solo ves reflejado el precio al realizar la venta.'],
            ['pregunta' => 'no me sale el precio del producto', 'respuesta' => 'Porque el precio puede cambiar en base a las compras hacia el proveedor (puede ser cambiante), solo ves reflejado el precio al realizar la venta.'],
            ['pregunta' => 'por que no esta visible el precio', 'respuesta' => 'Porque el precio puede cambiar en base a las compras hacia el proveedor (puede ser cambiante), solo ves reflejado el precio al realizar la venta.'],
            ['pregunta' => 'el precio aparece vacío en la tabla', 'respuesta' => 'Porque el precio puede cambiar en base a las compras hacia el proveedor (puede ser cambiante), solo ves reflejado el precio al realizar la venta.'],

            ['pregunta' => 'por que el stock esta en cero', 'respuesta' => 'Tal vez porque los productos se agotaron por las ventas y/o no se ha realizado la compra al proveedor correspondiente.'],
            ['pregunta' => 'el stock esta vacío', 'respuesta' => 'Tal vez porque los productos se agotaron por las ventas y/o no se ha realizado la compra al proveedor correspondiente.'],
            ['pregunta' => 'no hay stock', 'respuesta' => 'Tal vez porque los productos se agotaron por las ventas y/o no se ha realizado la compra al proveedor correspondiente.'],
            ['pregunta' => 'por que no hay productos en stock', 'respuesta' => 'Tal vez porque los productos se agotaron por las ventas y/o no se ha realizado la compra al proveedor correspondiente.'],
            ['pregunta' => 'los productos aparecen sin existencia', 'respuesta' => 'Tal vez porque los productos se agotaron por las ventas y/o no se ha realizado la compra al proveedor correspondiente.'],

            ['pregunta' => 'como puedo aplicar un descuento', 'respuesta' => 'Coloca la cantidad en dinero, no se coloca con porcentajes.'],
            ['pregunta' => 'como se agrega el descuento', 'respuesta' => 'Coloca la cantidad en dinero, no se coloca con porcentajes.'],
            ['pregunta' => 'como colocar descuento en una venta', 'respuesta' => 'Coloca la cantidad en dinero, no se coloca con porcentajes.'],
            ['pregunta' => 'como ingresar un descuento', 'respuesta' => 'Coloca la cantidad en dinero, no se coloca con porcentajes.'],
            ['pregunta' => 'puedo usar porcentaje para descuento', 'respuesta' => 'Coloca la cantidad en dinero, no se coloca con porcentajes.'],

            ['pregunta' => 'borre algo por error', 'respuesta' => 'Depende de la situación, si ves el boton de restaurar puedes hacer esta acción, caso contrario de no aparecer en tu tabla consulta al administrador.'],
            ['pregunta' => 'elimine algo por accidente', 'respuesta' => 'Depende de la situación, si ves el boton de restaurar puedes hacer esta acción, caso contrario de no aparecer en tu tabla consulta al administrador.'],
            ['pregunta' => 'puse eliminar sin querer', 'respuesta' => 'Depende de la situación, si ves el boton de restaurar puedes hacer esta acción, caso contrario de no aparecer en tu tabla consulta al administrador.'],
            ['pregunta' => 'como recupero un registro eliminado', 'respuesta' => 'Depende de la situación, si ves el boton de restaurar puedes hacer esta acción, caso contrario de no aparecer en tu tabla consulta al administrador.'],
            ['pregunta' => 'se borro un dato por error', 'respuesta' => 'Depende de la situación, si ves el boton de restaurar puedes hacer esta acción, caso contrario de no aparecer en tu tabla consulta al administrador.'],

            ['pregunta' => 'no tengo el archivo Excel o Txt', 'respuesta' => 'Puedes ingresar los datos de forma manual con el primer botón, o bien solicitar el formato al administrador.'],
            ['pregunta' => 'no tengo el formato de carga', 'respuesta' => 'Puedes ingresar los datos de forma manual con el primer botón, o bien solicitar el formato al administrador.'],
            ['pregunta' => 'puedo ingresar datos sin el archivo', 'respuesta' => 'Puedes ingresar los datos de forma manual con el primer botón, o bien solicitar el formato al administrador.'],
            ['pregunta' => 'me falta el archivo para importar datos', 'respuesta' => 'Puedes ingresar los datos de forma manual con el primer botón, o bien solicitar el formato al administrador.'],
            ['pregunta' => 'no tengo el documento para cargar datos', 'respuesta' => 'Puedes ingresar los datos de forma manual con el primer botón, o bien solicitar el formato al administrador.']

        ]);
    }
}
