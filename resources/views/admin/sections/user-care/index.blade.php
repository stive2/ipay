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
        'active' => __('User Care'),
    ])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("Tous les clients") }}</h5>
                <div class="table-btn-area">
                    @include('admin.components.link.custom',[
                        'href'          => "#custumer-import",
                        'class'         => "btn--base py-2 px-4 bg--info modal-btn",
                        'icon'          => "fas fa-upload me-1",
                        'text'          => __("Import"),
                        // 'permission'    => "admin.users.import",
                    ])
                    @if(count($users) > 0)
                        @include('admin.components.link.custom',[
                            'href'          => "#custumer-update",
                            'class'         => "btn--base py-2 px-4 bg--info modal-btn",
                            'icon'          => "fas fa-upload me-1",
                            'text'          => __("Update"),
                            // 'permission'    => "admin.users.update",
                        ])
                        @include('admin.components.link.custom',[
                            'text'          => __("Download"),
                            'icon'          => "fas fa-download me-1",
                            // 'permission'    => "admin.users.download",
                            'href'          => setRoute('admin.users.download'),
                            'class'         => "btn--base py-2 px-4 bg--primary",
                        ])
                    @endif
                    @include('admin.components.link.add-default',[
                        'class'         => "py-2 px-4 modal-btn",
                        'text'          => __("Add New"),
                        // 'permission'    => "admin.users.store",
                        'href'          => setRoute('user.register'),
                    ])
                </div>
                <div class="table-btn-area">
                    @include('admin.components.search-input',[
                        'name'  => 'user_search',
                    ])
                </div>
            </div>
            <div class="table-responsive">
                @include('admin.components.data-table.user-table',compact('users'))
            </div>
        </div>
        {{ get_paginate($users) }}
    </div>

    {{-- Import User --}}
    @include('admin.components.modals.custumer')
@endsection

@push('script')
    <script>
        itemSearch($("input[name=user_search]"),$(".user-search-table"),"{{ setRoute('admin.users.search') }}");
    </script>
@endpush
