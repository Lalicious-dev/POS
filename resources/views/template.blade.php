<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="dsescription" content="Sistema Punto de Venta" />
    <meta name="author" content="Eduardo Salinas" />
    <title>Sistema Punto de Venta - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <link href="{{ secure_asset('css/template.css')}}" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    @stack('css')

</head>
@auth

<body class="sb-nav-fixed">
    <x-navigation-header />
    <div id="layoutSidenav">
        <x-navigation-menu />
        <div id="layoutSidenav_content">
            <main>
                @yield('content')
            </main>
            <x-footer />
        </div>
    </div>
    <!-- Botón flotante para abrir el chatbot -->
    @can('usar-chatbot')
    <button id="chatbot-toggle" class="btn btn-dark rounded-circle p-3 position-fixed" style="bottom: 20px; right: 20px; z-index: 9999;">
        <i class="fa-solid fa-robot"></i>
    </button>
    @endcan
    <!-- Chatbot Widget -->
    <div id="chatbot-widget" class="card position-fixed shadow" style="bottom: 20px;  right: 20px; width: 90%; max-width: 350px; display: none; z-index: 10000;">
        <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
            <span>Asistente Virtual</span>
            <button onclick="cerrarChatbot()" class="btn-close btn-close-white btn-sm"></button>
        </div>
        <div class="card-body overflow-auto" id="chatbot-body" style="max-height: 300px; overflow-y: auto;"></div>
        <div class="card-footer p-2">
            <div class="input-group">
                <input type="text" id="mensaje-chatbot" class="form-control" placeholder="Escribe algo...">
                <button class="btn btn-dark" onclick="enviarMensajeChatbot()">Enviar</button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('chatbot-toggle').addEventListener('click', function() {
            document.getElementById('chatbot-widget').style.display = 'flex';
            document.getElementById('chatbot-toggle').style.display = 'none';
        });

        function cerrarChatbot() {
            document.getElementById('chatbot-widget').style.display = 'none';
            document.getElementById('chatbot-toggle').style.display = 'block';
        }

        function enviarMensajeChatbot() {
            
            const input = document.getElementById('mensaje-chatbot');
            const mensaje = input.value.trim();
            if (mensaje === '') return;

            mostrarEnChat('Tú', mensaje);
            input.value = '';

            fetch('/chatbot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        mensaje
                    })
                })
                .then(res => res.json())
                .then(data => {
                    mostrarEnChat('Bot', data.respuesta);
                });
        }

        function mostrarEnChat(usuario, texto) {
            const body = document.getElementById('chatbot-body');

            const row = document.createElement('div');
            row.classList.add('d-flex', 'mb-2');

            const msg = document.createElement('div');
            msg.classList.add('p-2', 'rounded', 'text-white');
            msg.style.maxWidth = '75%';
            msg.innerHTML = `<strong>${usuario}:</strong> ${texto}`;

            if (usuario === 'Tú') {
                row.classList.add('justify-content-end');
                msg.classList.add('bg-primary');
            } else {
                row.classList.add('justify-content-start');
                msg.classList.add('bg-secondary');
            }

            row.appendChild(msg);
            body.appendChild(row);
            body.scrollTop = body.scrollHeight;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
    @stack('js')

</body>
@endauth
@guest
@include('pages.401')
@endguest

</html>