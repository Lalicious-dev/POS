@extends('template')


@section('title','marcas')

@push('css')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
    <h1 class="mt-4 text-center">Marcas</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active"><a href="{{ route('panel') }}">Inicio</a></li>
        <li class="breadcrumb-item active">Marcas</li>
    </ol>
    @can('crear-marca')
    <div class="mb-4">
        <a href="{{route('marcas.create')}}"><button type="button" class="btn btn-primary">Añadir nuevo registro</button>
        </a>
    </div>
    @endcan
    @can('importar-marca')
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

                        <form action="{{route('marcas.importar')}}" enctype="multipart/form-data" id="form_carga_marcas" method="post">
                            @csrf
                            <label for="fileMarcas" class="form-label">Seleccionar Archivo de Carga (Excel):</label>
                            <input type="file" name="fileMarcas" id="fileMarcas" class="form-control" accept=".xls,.xlsx">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary mt-2" id="btnCargar">Cargar marcas</button>
                            </div>

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

                        <form action="{{route('marcas.importartxt')}}" enctype="multipart/form-data" id="form_carga_marcasT" method="post">
                            @csrf
                            <label for="fileMarcasT" class="form-label">Seleccionar Archivo de Carga (Txt):</label>
                            <input type="file" name="fileMarcasT" id="fileMarcasT" class="form-control" accept=".txt">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary mt-2" id="btnCargar">Cargar marcas</button>
                            </div>

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
            Tabla Marcas
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
                    @foreach($marcas as $marca)
                    <tr>
                        <td>
                            {{$marca->caracteristica->nombre}}
                        </td>
                        <td>
                            {{$marca->caracteristica->descripcion}}
                        </td>
                        <td>
                            @if ($marca->caracteristica->estado == 1)
                            <span class="badge rounded-pill text-bg-success d-inline">activo</span>
                            @else
                            <span class="badge rounded-pill text-bg-danger d-inline">eliminado</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Basic mixed styles example">
                                @can('editar-marca')
                                <form action="{{ route('marcas.edit',['marca'=>$marca]) }}" method="get">
                                    <button type="submit" class="btn btn-warning">Editar</button>
                                </form>
                                @endcan
                                @can('eliminar-marca')
                                @if($marca->caracteristica->estado == 1)
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$marca->id}}">Eliminar</button>
                                @else
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal-{{$marca->id}}">Restaurar</button>
                                @endif
                                @endcan

                            </div>
                        </td>
                    </tr>
                    <!-- Modal -->
                    <div class="modal fade" id="confirmModal-{{$marca->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Mensaje de confirmación</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    {{ $marca->caracteristica->estado == 1 ? '¿Seguro que quieres eliminar esta marca?' : '¿Seguro que quieres restaurar esta marca?' }}
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                    <form action="{{ route('marcas.destroy',['marca'=>$marca->id]) }}" method="post">
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