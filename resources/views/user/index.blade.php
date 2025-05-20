@extends('template')


@section('title','usuarios')

@push('css')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">

@endpush

@section('content')

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Usuarios</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Usuarios</li>
    </ol>
    @can('crear-user')
    <div class="mb-4">
        <a href="{{route('users.create')}}"><button type="button" class="btn btn-primary">Añadir nuevo usuario</button>
        </a>
    </div>
    @endcan
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla Usuarios
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
               <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
               </thead>
               <tbody>
                @foreach($users as $item)
                    <tr>
                        <td>
                            {{$item->name}}
                        </td> 
                        <td>
                            {{$item->email}}
                        </td>   
                        <td>
                            {{$item->getRoleNames()->first()}}
                        </td>                       
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                @can('editar-user')
                                <form action="{{ route('users.edit',['user'=>$item]) }}" method="get">
                                    <button type="submit" class="btn btn-warning">Editar</button>
                                </form>
                                @endcan
                                @can('eliminar-user')
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$item->id}}">Eliminar</button>
                                @endcan
                                
                            </div>
                        </td>                
                    </tr>
                     <!-- Modal de confirmación-->
                     <div class="modal fade" id="confirmModal-{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Mensaje de confirmación</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Seguro que quieres eliminar el usuario?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                    <form action="{{ route('users.destroy',['user'=>$item->id]) }}" method="post">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-danger">Si, confirmo esta acción</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
               </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- Cargar SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
    <script>
        let message = "{{ session('success') }}";
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        Toast.fire({
            icon: "success",
            title: message
        });
    </script>
    @endif
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="{{ secure_asset('js/datatables-simple-demo.js') }}"></script>
@endpush