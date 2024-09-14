<!DOCTYPE html>
<html lang="{{ get_default_language_code() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $basic_settings->sitename(__($page_title??'')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800;900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <script src="{{ asset('public/backend/js/google-app-agent.js') }}"></script>

    @include('agent.partials.header-assets')

    @stack('css')
    <script>
        function sendCoordinates(latitude, longitude, id) {
            const url = 'http://127.0.0.1:82/agent/storeCoordinate'; // L'URL API

            // Données à envoyer dans le corps de la requête
            const data = {
                latitude: latitude,
                longitude: longitude,
                id: id
            };

            // Options de la requête fetch
            const options = {
                method: 'POST', // Méthode de la requête
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token')
                },
                body: JSON.stringify(data) // Conversion des données en JSON
            };

            // Envoi de la requête POST
            fetch(url, options)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur lors de l\'envoi des coordonnées');
                }
                return response.json(); // Conversion de la réponse en JSON
            })
            .then(data => {
                console.log('Coordonnée enregistrée avec succès:', data);
                // Vous pouvez ajouter d'autres actions ici, par exemple afficher un message à l'utilisateur
            })
            .catch(error => {
                console.error('Erreur:', error);
                // Gérer l'erreur, par exemple afficher un message d'erreur à l'utilisateur
            });
        }

        document.addEventListener("DOMContentLoaded", function(){

            const email = @json(Auth::user()->email); // Remplacez par l'email de l'utilisateur
            const password = '123456789'; // Remplacez par le mot de passe de l'utilisateur
            // loginUser(email, password);

            // HTML5 geolocation.
            if (navigator.geolocation) {
                setInterval(function(){
                        navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const pos = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                            };

                            sendCoordinates(pos.lat, pos.lng, @json(Auth::user()->id));
                        });
                }, 10000);
            }
        });
    </script>
</head>
<body class="{{ selectedLangDir() ?? "ltr"}}">

    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        Start body overlay
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
    <div id="body-overlay" class="body-overlay"></div>
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        End body overlay
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        Start Dashboard
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
    <div class="page-wrapper">
        @include('agent.partials.side-nav')
        <div class="main-wrapper">
            <div class="main-body-wrapper">
                @include('agent.partials.top-nav')
                @yield('content')
            </div>
        </div>
    </div>
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        End Dashboard
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
    <a href="{{ setRoute('agent.receive.money.index') }}" class="qr-scan"><i class="fas fa-qrcode"></i></a>
    @include('agent.partials.footer-assets')
    @include('agent.partials.push-notification')
    @stack('script')
</body>



</html>
