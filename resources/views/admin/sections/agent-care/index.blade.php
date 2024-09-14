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
                    @include('admin.components.button.custom',[
                        'type'          => "button",
                        'class'         => "empty-wallet w-100",
                        'text'          => "Empty Wallet",
                    ])
                </div>
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
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCaJWRLynYXmkIDFdHhA3l8uqVuMFVHNoE"></script>
    @endif
@endsection

@push('script')
    <script>
        itemSearch($("input[name=agent_search]"),$(".agent-search-table"),"{{ setRoute('admin.agents.search') }}");
        $(".empty-wallet").click(function(){
            var actionRoute = "{{ setRoute('admin.agents.auto.wallet.balance.substract') }}";
            var target      = "AZE";
            var message     = `Are you sure to empty all agent wallet?`;
            openDeleteModal(actionRoute,target,message,"Approve","POST");
        });
    </script>
    @if(Route::currentRouteName() == "admin.agents.locate")
         <script type="module" src="{{ asset('public/backend/js/google-app.js') }}"></script>
    @endif
@endpush

