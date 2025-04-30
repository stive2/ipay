@extends('admin.layouts.master')

@push('css')
@endpush

@section('page-title')
    @include('admin.components.page-title', ['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb', [
        'breadcrumbs' => [
            [
                'name' => __('Dashboard'),
                'url' => setRoute('admin.dashboard'),
            ],
        ],
        'active' => __('Agent Care'),
    ])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("All Agent") }}</h5>
                <div class="table-btn-area">
                    @include('admin.components.link.custom',[
                        'href'          => "#agent-import",
                        'class'         => "btn--base py-2 px-4 bg--info modal-btn",
                        'icon'          => "fas fa-upload me-1",
                        'text'          => __("Import"),
                        // 'permission'    => "admin.agents.import",
                    ])
                    @if(count($agents) > 0)
                        @include('admin.components.link.custom',[
                            'text'          => __("Download"),
                            'icon'          => "fas fa-download me-1",
                            // 'permission'    => "admin.agents.download",
                            'href'          => setRoute('admin.agents.download'),
                            'class'         => "btn--base py-2 px-4 bg--primary",
                        ])
                    @endif
                    @include('admin.components.link.add-default',[
                        'class'         => "py-2 px-4 modal-btn",
                        'text'          => __("Add New"),
                        // 'permission'    => "admin.agents.store",
                        'href'          => setRoute('agent.register'),
                    ])
                </div>
                {{--  <div class="table-btn-area">
                    @include('admin.components.button.custom',[
                        'type'          => "button",
                        'class'         => "empty-wallet w-100 bg--danger",
                        'text'          => "Vider les wallets",
                    ])
                </div>  --}}
                <div class="table-btn-area">
                    @include('admin.components.search-input',[
                        'name'  => 'agent_search',
                    ])
                </div>
            </div>
            <div class="table-responsive">
                @include('admin.components.data-table.agent-table',compact('agents'))
            </div>
        </div>
        {{ get_paginate($agents) }}
    </div>
    @if(Route::currentRouteName() == "admin.agents.locate")
        <div class="container mt-4">
            <div class="card" style="border-radius: 10px;">
                <div class="card-body p-0">
                    <div id="map" style="height: 400px; border-radius: 10px;"></div>
                </div>
            </div>
        </div>
        <!-- Inclusion de l'API Google Maps -->
        {{--  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCaJWRLynYXmkIDFdHhA3l8uqVuMFVHNoE"></script>  --}}
    @endif

    {{-- Import Agent --}}
    @include('admin.components.modals.agent')
@endsection

@push('script')
    <script>
        itemSearch($("input[name=agent_search]"),$(".agent-search-table"),"{{ setRoute('admin.agents.search') }}");
        $(".empty-wallet").click(function(){
            var actionRoute = "{{ setRoute('admin.agents.auto.wallet.balance.substract') }}";
            var target      = "AZE";
            var message     = `Êtes vous sûr de vouloir vider les wallets de tous les agents ?`;
            openDeleteModal(actionRoute,target,message,"Approve","POST");
        });
    </script>
    @if(Route::currentRouteName() == "admin.agents.locate")
         <script type="module" src="{{ asset('public/backend/js/google-app.js') }}"></script>
    @endif
@endpush

