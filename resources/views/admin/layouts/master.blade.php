<!DOCTYPE html>
<html lang="{{ get_default_language_code() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ (isset($page_title) ? __($page_title) : __("Admin")) }}</title>
    <!-- favicon -->
    <link rel="shortcut icon" href="{{ get_fav($basic_settings) }}" type="image/x-icon">
    <link href="//fonts.googleapis.com/css2?family=Karla:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <!-- fontawesome css link -->
    <link rel="stylesheet" href="{{ asset('public/backend/css/fontawesome-all.min.css') }}">
    <!-- bootstrap css link -->
    <link rel="stylesheet" href="{{ asset('public/backend/css/bootstrap.min.css') }}">
    <!-- line-awesome-icon css -->
    <link rel="stylesheet" href="{{ asset('public/backend/css/line-awesome.min.css') }}">
    <!-- animate.css -->
    <link rel="stylesheet" href="{{ asset('public/backend/css/animate.css') }}">
    <!-- nice select css -->
    <link rel="stylesheet" href="{{ asset('public/backend/css/nice-select.css') }}">
    <!-- select2 css -->
    <link rel="stylesheet" href="{{ asset('public/backend/css/select2.min.css') }}">
    <!-- rte css -->
    <link rel="stylesheet" href="{{ asset('public/backend/css/rte_theme_default.css') }}">
    <!-- Popup  -->
    <link rel="stylesheet" href="{{ asset('public/backend/library/popup/magnific-popup.css') }}">
    <!-- Light case   -->
    <link rel="stylesheet" href="{{ asset('public/backend/css/lightcase.css') }}">

    <!-- Fileholder CSS CDN -->
    <link rel="stylesheet" href="https://appdevs.cloud/cdn/fileholder/v1.0/css/fileholder-style.css" type="text/css">

    <!-- main style css link -->
    <link rel="stylesheet" href="{{ asset('public/backend/css/style.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        var markers = {};
        var map;

        //navigator.serviceWorker.register('/ipay/service-worker.js')
            //.then(function(registration) {
            //    console.log('Service Worker registered with scope:', registration.scope);
                //Fonction qui initialise la carte
                window.initMap = async function() {
                    map = L.map('map').setView([3.8480, 11.5021], 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy Intelligentsia SA'
                    }).addTo(map);

                    // Options de la requête fetch
                    const options = {
                        method: 'GET', // Méthode de la requête
                        headers: {
                            'Content-Type': 'application/json'
                            , 'Accept': 'application/json'
                            , 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    };

                    // Récupération des coordonnées des utilisateurs via l'API
                    fetch('http://127.0.0.1:81/admin/coordinates', options)
                        .then(response => response.json())
                        .then(coordinates => {
                            const data = coordinates.data;
                            const markerPromises = data.map(async coordinate => {
                                const marker = await createMarker(coordinate.id, coordinate['latest_coordinate'].latitude, coordinate['latest_coordinate'].longitude, coordinate['username']);
                                markers[coordinate.id] = marker; // Stocker le marqueur avec l'ID comme clé
                            });

                            initLocateButtons();

                            // Assurez-vous que tous les marqueurs sont créés avant de souscrire à Pusher
                            Promise.all(markerPromises).then(() => {
                                console.log("All markers initialized: ", markers);

                                // Abonnement à Pusher après que tous les marqueurs aient été créés
                                window.Pusher = Pusher;
                                Pusher.logToConsole = true;
                                var pusher = new Pusher('fa5ebc6f9f6d1f332663', {
                                    cluster: 'eu'
                                    , forceTLS: true
                                , });

                                var channel = pusher.subscribe('agent-coordinates');
                                channel.bind('coordinate-event', function(data) {
                                    updateMarker(data.coordinate['agent_id'], data.coordinate['latitude'], data.coordinate['longitude']);
                                });
                            });

                            // Centrer la carte sur le premier utilisateur (ou ajuster la logique selon vos besoins)
                            if (coordinates.length > 0) {
                                map.setCenter({
                                    lat: parseFloat(coordinates[0].latitude)
                                    , lng: parseFloat(coordinates[0].longitude)
                                });
                            }
                        }).catch(error => console.error('Error fetching coordinates:', error));
                }

                // Fonction pour créer un Marqueur
                window.createMarker = async function(id, lat, lng, agentName) {
                    // Création d'une icône personnalisée
                    const customIcon = L.icon({
                        iconUrl: 'https://i.pinimg.com/736x/64/81/22/6481225432795d8cdf48f0f85800cf66.jpg', // URL de l'image pour l'icône
                        iconSize: [45, 45], // Taille de l'icône
                        iconAnchor: [22, 45], // Point d'ancrage de l'icône (son "pied")
                        popupAnchor: [0, -45] // Point d'ancrage pour les fenêtres popup
                    });

                    // Création d'un marqueur avec l'icône personnalisée
                    const marker = L.marker([parseFloat(lat), parseFloat(lng)], {
                        icon: customIcon
                    }).addTo(map);

                    // Ajout d'un popup (optionnel)
                    marker.bindPopup(`<b>ID Agent:</b> ${id}<br><b>Nom:</b> ${agentName}`).openPopup();

                    return marker;
                }

                // Fonction pour mettre à jour un marqueur existant
                function updateMarker(id, lat, lng) {
                    if (markers[id]) {
                        // Met à jour la position du marqueur existant
                        markers[id].setLatLng([parseFloat(lat), parseFloat(lng)]);
                        console.log('Marqueur mis à jour : ', markers[id]);
                    } else {
                        console.log('Le marqueur n\'existe pas pour ID:', id, ', création d\'un nouveau...');
                        // Si le marqueur n'existe pas, crée un nouveau marqueur
                        markers[id] = createMarker(id, lat, lng);
                    }
                }

                //Fonction pour initialiser les boutons de localisation
                function initLocateButtons() {
                    const localiserButtons = document.querySelectorAll('.localiser-btn');

                    localiserButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const userId = this.getAttribute('data-user-id');
                            const userName = this.getAttribute('data-user-name');
                            const latitude = parseFloat(this.getAttribute('data-latitude'));
                            const longitude = parseFloat(this.getAttribute('data-longitude'));
                            const userLatLng = [latitude, longitude];

                            const marker = markers[userId];
                            if (marker) {
                                // Centrer la carte sur les coordonnées de l'utilisateur
                                map.setView(userLatLng, 17); // Zoom 17 pour recentrer la carte

                                // Ouvre un popup sur le marqueur avec les informations de l'utilisateur
                                marker.bindPopup(`<b>ID Agent:</b> ${userId}<br><b>Nom:</b> ${userName}`).openPopup();

                                console.log('Localiser l\'utilisateur avec ID:', userId);
                                console.log('Coordonnées:', latitude, longitude);
                            } else {
                                console.log('Aucun marqueur trouvé pour l\'agent avec ID:', agentId);
                            }
                        });
                    });
                }

                //Initilisation de la carte
                window.onload = async function() {
                    await initMap();
                };
            //}).catch(function(error) {
            //    console.error('Service Worker registration failed:', error);
            //});

    </script>

    <style>
        .fileholder-single-file-view {
            min-width: 130px;
        }

    </style>
    @stack('css')
</head>
<body>

    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    Start Admin
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->
    <div class="page-wrapper">
        <div id="body-overlay" class="body-overlay"></div>
        @include('admin.partials.right-settings')
        @include('admin.partials.side-nav-mini')
        @include('admin.partials.side-nav')
        <div class="main-wrapper">
            <div class="main-body-wrapper">
                <nav class="navbar-wrapper">
                    <div class="dashboard-title-part">
                        @yield('page-title')
                        @yield('breadcrumb')
                    </div>
                </nav>
                <div class="body-wrapper">
                    @yield('content')
                </div>
            </div>
            @include('admin.partials.footer')
        </div>
    </div>
    <!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End Admin
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

    <!-- jquery -->
    <script src="{{ asset('public/backend/js/jquery-3.6.0.min.js') }}"></script>
    <!-- bootstrap js -->
    <script src="{{ asset('public/backend/js/bootstrap.bundle.min.js') }}"></script>
    <!-- smooth scroll js -->
    <script src="{{ asset('public/backend/js/smoothscroll.min.js') }}"></script>
    <!-- easypiechart js -->
    <script src="{{ asset('public/backend/js/jquery.easypiechart.js') }}"></script>
    <!-- apexcharts js -->
    <script src="{{ asset('public/backend/js/apexcharts.min.js') }}"></script>
    <!-- chart js -->
    <script src="{{ asset('public/backend/js/chart.js') }}"></script>
    <!-- nice select js -->
    <script src="{{ asset('public/backend/js/jquery.nice-select.js') }}"></script>
    <!-- select2 js -->
    <script src="{{ asset('public/backend/js/select2.min.js') }}"></script>
    <!-- rte js -->
    <script src="{{ asset('public/backend/js/rte.js') }}"></script>
    <!-- rte plugins js -->
    <script src='{{ asset('public/backend/js/all_plugins.js') }}'></script>
    <!--  Popup -->
    <script src="{{ asset('public/backend/library/popup/jquery.magnific-popup.js') }}"></script>
    <!--  ligntcase -->
    <script src="{{ asset('public/backend/js/lightcase.js') }}"></script>
    <!--  Rich text Editor JS -->
    <script src="{{ asset('public/backend/js/ckeditor.js') }}"></script>
    <!-- main -->
    <script src="{{ asset('public/backend/js/main.js') }}"></script>
    @stack('script')

    @include('admin.partials.notify')
    @include('admin.partials.auth-control')
    @include('admin.partials.push-notification')

    <script>
        var fileHolderAfterLoad = {};

    </script>

    <script src="https://appdevs.cloud/cdn/fileholder/v1.0/js/fileholder-script.js" type="module"></script>
    <script type="module">
        import { fileHolderSettings } from "https://appdevs.cloud/cdn/fileholder/v1.0/js/fileholder-settings.js";
    import { previewFunctions } from "https://appdevs.cloud/cdn/fileholder/v1.0/js/fileholder-script.js";

    var inputFields = document.querySelector(".file-holder");
    fileHolderAfterLoad.previewReInit = function(inputFields){
        previewFunctions.previewReInit(inputFields)
    };

    fileHolderSettings.urls.uploadUrl = "{{ setRoute('fileholder.upload') }}";
    fileHolderSettings.urls.removeUrl = "{{ setRoute('fileholder.remove') }}";

</script>

    <script>
        function fileHolderPreviewReInit(selector) {
            var inputField = document.querySelector(selector);
            fileHolderAfterLoad.previewReInit(inputField);
        }

    </script>

    <script>
        // lightcase
        $(window).on('load', function() {
            $("a[data-rel^=lightcase]").lightcase();
        })

    </script>

    <script>
        function openDeleteModal(URL, target, message, actionBtnText = "Remove", method = "DELETE") {
            if (URL == "" || target == "") {
                return false;
            }

            if (message == "") {
                message = "Are you sure to delete ?";
            }
            var method = `<input type="hidden" name="_method" value="${method}">`;
            openModalByContent({
                    content: `<div class="card modal-alert border-0">
                      <div class="card-body">
                          <form method="POST" action="${URL}">
                              <input type="hidden" name="_token" value="${laravelCsrf()}">
                              ${method}
                              <div class="head mb-3">
                                  ${message}
                                  <input type="hidden" name="target" value="${target}">
                              </div>
                              <div class="foot d-flex align-items-center justify-content-between">
                                  <button type="button" class="modal-close btn btn--info">{{ __('Close') }}</button>
                                  <button type="submit" class="alert-submit-btn btn btn--danger btn-loading">${actionBtnText}</button>
                              </div>
                          </form>
                      </div>
                  </div>`
                , },

            );
        }

    </script>

    @stack('script')

</body>
</html>
