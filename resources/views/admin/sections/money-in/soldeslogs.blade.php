@extends('admin.layouts.master')

@push('css')

@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __("Historique des soldes")])
@endsection

@section('content')
<div class="table-area">
    <div class="table-wrapper">
        <div class="table-header">
            <h5 class="title">{{ $page_title }}</h5>
            <div class="table-btn-area">
                {{--  @include('admin.components.button.custom',[
                    'type'          => "button",
                    'class'         => "btn--base py-2 px-4 bg--info modal-btn",
                    'class'         => "send-solde bg--info w-100",
                    'text'          => "Notifier les soldes du jour",
                ])  --}}
                @include('admin.components.link.custom',[
                    'href'          => "#soldes-import",
                    'class'         => "btn--base py-2 px-4 bg--info modal-btn",
                    'icon'          => "fas fa-upload me-1",
                    'text'          => __("Import"),
                    // 'permission'    => "admin.soldes.import.data",
                ])
                @if(count($soldes) > 0)
                    @include('admin.components.link.custom',[
                        'text'          => __("Download"),
                        'icon'          => "fas fa-download me-1",
                        // 'permission'    => "admin.soldes.export.data",
                        'href'          => setRoute('admin.soldes.download'),
                        'class'         => "btn--base py-2 px-4 bg--primary",
                    ])
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __("ID") }}</th>
                        <th>{{ __("Matricule") }}</th>
                        <th>{{ __("Noms") }}</th>
                        <th>{{ __("Compte") }}</th>
                        <th>{{ __("Solde") }}</th>
                        {{--  <th>{{ __("Client notifié") }}</th>  --}}
                        <th>{{ __("Date") }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($soldes ?? []  as $key => $item)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $item->matricule }}</td>
                            <td>{{ $item->fullname }}</td>
                            <td>{{ $item->compte }}</td>
                            <td>{{ getAmount($item->solde,2) }}</td>
                            {{--  <td>
                                @if($item->notifie == 1)
                                    <span style="color: green">Oui</span>
                                @else
                                    <span style="color: red">Non</span>
                                @endif
                            </td>  --}}
                            <td>{{ $item->date->format('d-m-Y') }}</td>
                        </tr>
                    @empty
                         @include('admin.components.alerts.empty',['colspan' => 6])
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ get_paginate($soldes) }}
    </div>
</div>
{{-- Import Agent --}}
@include('admin.components.modals.soldes')

@endsection

@push('script')
    <script>
        $(".send-solde").click(function(){
            var actionRoute = "{{ setRoute('admin.soldes.notification') }}";
            var target      = "AZY";
            var message     = `Confirmer l'envoie par sms des soldes du jour`;
            openDeleteModal(actionRoute,target,message,"Envoyer","GET");
        });
    </script>
@endpush
