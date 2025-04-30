@extends('admin.layouts.master')

@push('css')
    <style>
        .fileholder {
            min-height: 194px !important;
        }

        .fileholder-files-view-wrp.accept-single-file .fileholder-single-file-view,.fileholder-files-view-wrp.fileholder-perview-single .fileholder-single-file-view{
            height: 150px !important;
        }
    </style>
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
    ], 'active' => __("Configuration des agences")])
@endsection

@section('content')
    <div class="table-area">
        <div class="table-wrapper">
            <div class="table-header">
                <h5 class="title">{{ __("Configuration des agences") }}</h5>
                <div class="table-btn-area">
                    @include('admin.components.link.custom',[
                        'href'          => "#agence-import",
                        'class'         => "btn--base py-2 px-4 bg--info modal-btn",
                        'icon'          => "fas fa-upload me-1",
                        'text'          => __("Import"),
                        // 'permission'    => "admin.agents.import",
                    ])
                    @if(count($agences) > 0)
                        @include('admin.components.link.custom',[
                            'text'          => __("Exporter"),
                            'icon'          => "fas fa-download me-1",
                            // 'permission'    => "admin.agences.download",
                            'href'          => setRoute('admin.agence.export'),
                            'class'         => "btn--base py-2 px-4 bg--primary",
                        ])
                    @endif
                    @include('admin.components.link.add-default',[
                        'href'          => "#agence-add",
                        'class'         => "py-2 px-4 modal-btn",
                        'text'          => __("Add New"),
                        'permission'    => "admin.agences.store",
                    ])
                </div>
            </div>
            <div class="table-responsive">
                @include('admin.components.data-table.agence-table',[
                    'data'  => $agences
                ])
            </div>
        </div>
        {{ get_paginate($agences) }}
    </div>

    {{-- agences Edit Modal --}}
    @include('admin.components.modals.edit-agence')

    {{-- agences Add Modal --}}
    @include('admin.components.modals.add-agence')

    {{-- Import Agent --}}
    @include('admin.components.modals.agence')

@endsection

@push('script')
    <script>

        getAllCountries("{{ setRoute('global.countries') }}"); // get all country and place it country select input
        $(document).ready(function() {
            reloadAllCountries("select[name=country]");

            // Country Field On Change
            $(document).on("change",".country-select",function() {
                var selectedValue = $(this);
                var countryCode = selectedValue.val();
                var countryName = selectedValue.find("option:selected").text();
            });

        });

        function keyPressagenceView(select) {
            var selectedValue = $(select);
            selectedValue.parents("form").find("input[name=code],input[name=agence_code]").keyup(function(){
                selectedValue.parents("form").find(".selcted-agence").text($(this).val());
            });
        }

        $("input[name=type],input[name=agence_type]").siblings(".switch").click(function(){
            setTimeout(() => {
                var agenceType = $(this).siblings("input[name=type],input[name=agence_type]").val();
                var readOnly = true;
                if(agenceType == "CRYPTO") {
                    readOnly = false;
                }
                // readOnlyAddRemove($(this),readOnly);
            }, 200);
        });

        function readOnlyAddRemove (select,readOnly) {
            var selectedValue = $(select);
            selectedValue.parents("form").find("input[name=name],input[name=agence_name]");//.prop("readonly",readOnly);
            selectedValue.parents("form").find("input[name=code],input[name=agence_code]");//.prop("readonly",readOnly);
            selectedValue.parents("form").find("input[name=symbol],input[name=agence_symbol]");//.prop("readonly",readOnly);
            // selectedValue.parents("form").find(".selcted-agence").text(agenceCode);
        }

        $(".delete-modal-button").click(function(){
            var oldData = JSON.parse($(this).parents("tr").attr("data-item"));

            var actionRoute =  "{{ setRoute('admin.agence.delete') }}";
            var target      = oldData.code;
            var message     = `Are you sure to delete <strong>${oldData.code}</strong> agence?`;

            openDeleteModal(actionRoute,target,message);
        });

        itemSearch($("input[name=agence_search]"),$(".agence-search-table"),"{{ setRoute('admin.agence.search') }}",1);
    </script>
@endpush
