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
    ], 'active' => __("Historique ouverture & fermeture des collectes")])
@endsection

@section('content')
<div class="table-area">
    <div class="table-wrapper">
        <div class="table-header">
            <h5 class="title">{{ $page_title }}</h5>
            <div class="table-btn-area">
                @if(status_collecte())
                    @include('admin.components.button.custom',[
                        'type'          => "button",
                        'class'         => "close-collect bg--success w-100",
                        'text'          => "Fermer la collecte",
                    ])
                @else
                    @include('admin.components.button.custom',[
                        'type'          => "button",
                        'class'         => "open-collect bg--danger w-100",
                        'text'          => "Ouvrir la collecte",
                    ])
                @endif
            </div>
            @if(count($logs) > 0)
                <div class="table-btn-area">
                    <a href="{{ setRoute('admin.web.settings.export.collect') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
                </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __("ID") }}</th>
                        <th>{{ __("Utilisateur") }}</th>
                        <th>{{ __("Action") }}</th>
                        <th>{{ __("Time") }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs ?? []  as $key => $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->admin }}</td>
                            <td>{{ $item->action }}</td>
                            <td>{{ $item->created_at->format('d-m-y h:i:s') }}</td>
                        </tr>
                    @empty
                         @include('admin.components.alerts.empty',['colspan' => 11])
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ get_paginate($logs) }}
    </div>
</div>
@endsection

@push('script')
    <script>
        $(".close-collect").click(function(){
            var actionRoute = "{{ setRoute('admin.web.settings.close.collect') }}";
            var target      = "AZO";
            var message     = `Êtes vous sûr de vouloir fermer la collecte ?`;
            openDeleteModal(actionRoute,target,message,"Approve","GET");
        });

        $(".open-collect").click(function(){
            var actionRoute = "{{ setRoute('admin.web.settings.open.collect') }}";
            var target      = "AZE";
            var message     = `Êtes vous sûr de vouloir ouvrir la collecte ?`;
            openDeleteModal(actionRoute,target,message,"Approve","GET");
        });
    </script>
@endpush
