@extends('template')


@section('title','presentaciones')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">

@endpush

@section('content')

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

<div class="container-fluid px-4">
    <h1 class="mt-4 text-center">Presentaciones</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Presentaciones</li>
    </ol>
    @can('crear-presentacione')
    <div class="mb-4">
        <a href="{{route('presentaciones.create')}}"><button type="button" class="btn btn-primary">Añadir nuevo registro</button>
        </a>
    </div>
    @endcan
    @can('importar-presentacione')
    <div class="d-flex mb-4">
        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#exModal">
            Añadir Excel
        </button>

        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#txtModal">
            Añadir TXT
        </button>
    </div>
    <!-- Modal para excel -->
    <div class="modal fade" id="exModal" tabindex="-1" aria-labelledby="exModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exModalLabel">Cargar datos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">

                        <form action="{{route('presentaciones.importar')}}" enctype="multipart/form-data" id="form_carga_presentaciones" method="post">
                            @csrf
                            <label for="filePresentaciones" class="form-label">Seleccionar Archivo de Carga (Excel):</label>
                            <input type="file" name="filePresentaciones" id="filePresentaciones" class="form-control" accept=".xls,.xlsx">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary mt-2" id="btnCargar">Cargar presentaciones</button>
                            </div>
                            @error('filePresentaciones')
                            <small class="text-danger">{{'*'.$message}}</small>
                            @enderror
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para txt-->
    <div class="modal fade" id="txtModal" tabindex="-1" aria-labelledby="txtModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="txtModalLabel">Cargar datos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">

                        <form action="{{route('presentaciones.importartxt')}}" enctype="multipart/form-data" id="form_carga_presentacionesT" method="post">
                            @csrf
                            <label for="filePresentacionesT" class="form-label">Seleccionar Archivo de Carga (Txt):</label>
                            <input type="file" name="filePresentacionesT" id="filePresentacionesT" class="form-control" accept=".txt">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary mt-2" id="btnCargar">Cargar presentaciones</button>
                            </div>
                            @error('filePresentacionesT')
                            <small class="text-danger">{{'*'.$message}}</small>
                            @enderror
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endcan
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Tabla Presentaciones
        </div>
        <div class="card-body">
            <table id="datatablesSimple" class="table table-striped">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripcion</th>
                        <th>Estado</th>
                        <th>Acciones</th>


                    </tr>
                </thead>
                <tbody>
                    @foreach($presentaciones as $presentacione)
                    <tr>
                        <td>
                            {{$presentacione->caracteristica->nombre}}
                        </td>
                        <td>
                            {{$presentacione->caracteristica->descripcion}}
                        </td>
                        <td>
                            @if ($presentacione->caracteristica->estado == 1)
                            <span class="badge rounded-pill text-bg-success d-inline">activo</span>
                            @else
                            <span class="badge rounded-pill text-bg-danger d-inline">eliminado</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                @can('editar-presentacione')
                                <form action="{{ route('presentaciones.edit',['presentacione'=>$presentacione]) }}" method="get">
                                    <button type="submit" class="btn btn-warning">Editar</button>
                                </form>
                                @endcan
                                @can('eliminar-presentacione')
                                @if($presentacione->caracteristica->estado == 1)
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$presentacione->id}}">Eliminar</button>
                                @else
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$presentacione->id}}">Restaurar</button>
                                @endif
                                @endcan

                            </div>
                        </td>
                    </tr>
                    <!-- Modal -->
                    <div class="modal fade" id="confirmModal-{{$presentacione->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Mensaje de confirmación</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    {{ $presentacione->caracteristica->estado == 1 ? '¿Seguro que quieres eliminar esta presentación?' : '¿Seguro que quieres restaurar esta presentación?' }}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                    <form action="{{ route('presentaciones.destroy',['presentacione'=>$presentacione->id]) }}" method="post">
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
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>
<script src="{{ asset('js/datatables-simple-demo.js') }}"></script>
@endpush