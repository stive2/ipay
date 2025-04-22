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
    ], 'active' => __("withdraw Log")])
@endsection

@section('content')
<div class="table-area">
    <div class="table-wrapper">
        <div class="table-header">
            <h5 class="title">{{ $page_title }}</h5>
            @if(count($transactions) > 0)
                @if(Route::currentRouteName() == "admin.money.out.pending")
                    <div class="table-btn-area">
                        <button type="button" class="btn btn--base approvedBtn">{{ __("Tout valider") }}</button>
                    </div>
                    <button type="button" class="btn btn--base filterBtnP">{{ __("Exporter") }}</button>
                    {{--  <div class="table-btn-area">
                        <a href="{{ setRoute('admin.money.out.export.pending') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
                    </div>  --}}
                @elseif(Route::currentRouteName() == "admin.money.out.complete")
                    <button type="button" class="btn btn--base filterBtnV">{{ __("Exporter") }}</button>
                    {{--  <div class="table-btn-area">
                        <a href="{{ setRoute('admin.money.out.export.validated') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
                    </div>  --}}
                @elseif(Route::currentRouteName() == "admin.money.out.canceled")
                    <button type="button" class="btn btn--base filterBtnC">{{ __("Exporter") }}</button>
                    {{--  <div class="table-btn-area">
                        <a href="{{ setRoute('admin.money.out.export.rejected') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
                    </div>  --}}
                @elseif(Route::currentRouteName() == "admin.money.out.cbsPending")
                    <div class="table-btn-area">
                        <button type="button" class="btn btn--base integratedBtn bg--danger">{{ __("Marquer comme intégré au CBS") }}</button>
                    </div>
                    <div class="table-btn-area">
                        <button type="button" class="btn btn--base filterBtn">{{ __("Exporter") }}</button>
                    </div>
                    {{--  <div class="table-btn-area">
                        <a href="{{ setRoute('admin.money.out.export.cbspending') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Fichier d'intégration") }}</a>
                    </div>  --}}
                @elseif(Route::currentRouteName() == "admin.money.out.cbsCompleted")
                    <button type="button" class="btn btn--base filterBtnCC">{{ __("Exporter") }}</button>
                    {{--  <div class="table-btn-area">
                        <a href="{{ setRoute('admin.money.out.export.integrated') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
                    </div>  --}}
                @else
                    <button type="button" class="btn btn--base filterBtnCE">{{ __("Exporter") }}</button>
                    {{--  <div class="table-btn-area">
                        <a href="{{ setRoute('admin.money.out.export.data') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
                    </div>  --}}
                @endif

            @endif
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __("Ref Collecte") }}</th>
                        <th>{{ __("Client") }}</th>
                        <th>{{ __("Phone") }}</th>
                        <th>{{ __("Matricule") }}</th>
                        <th>{{ __("N° Compte") }}</th>
                        <th>{{ __("Montant recu") }}</th>
                        {{--  <th>{{ __("Method") }}</th>  --}}
                        <th>{{ __(("Status")) }}</th>
                        <th>{{ __("Time") }}</th>
                        <th>{{__("action")}}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions  as $key => $item)

                        <tr>
                            <td>{{ $item->trx_id }}</td>
                            <td>
                                @if($item->user_id != null)
                                <a href="{{ setRoute('admin.users.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                @elseif($item->agent_id != null)
                                <a href="{{ setRoute('admin.agents.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                @elseif($item->merchant_id != null)
                                <a href="{{ setRoute('admin.merchants.details',$item->creator->username) }}">{{ $item->creator->fullname }}</a>
                                @endif
                            </td>
                            <td>
                               {{ $item->creator->full_mobile ?? '' }}
                            </td>
                            <td>
                                {{--  @if($item->user_id != null)
                                     {{ __("USER") }}
                                @elseif($item->agent_id != null)
                                     {{ __("AGENT") }}
                                @elseif($item->merchant_id != null)
                                     {{ __("MERCHANT") }}
                                @endif  --}}
                                {{ $item->creator->matricule ?? '' }}
                            </td>
                            <td>
                                {{ $item->creator->rib ?? '' }}
                             </td>
                            <td>{{ number_format($item->request_amount,2) }} {{ get_default_currency_code() }}</td>
                            {{--  <td><span class="text--info">{{ @$item->currency->name }}</span></td>  --}}
                            <td>
                                <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span>
                            </td>
                            <td>{{ $item->created_at->format('d-m-y h:i:s A') }}</td>
                            <td>
                                @include('admin.components.link.info-default',[
                                    'href'          => setRoute('admin.money.out.details', $item->id),
                                    'permission'    => "admin.money.out.details",
                                ])

                            </td>
                        </tr>
                    @empty
                         @include('admin.components.alerts.empty',['colspan' => 9])
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ get_paginate($transactions) }}
    </div>
</div>

<div class="modal fade" id="approvedModal" tabindex="-1" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="approvedModalLabel">
                <h5 class="modal-title">{{ __("Approved All Confirmation") }} </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.money.out.approved.all') }}" method="POST">

                    @csrf
                    @method("GET")
                    <div class="row mb-10-none">
                        <div class="col-xl-12 col-lg-12 form-group">
                           <p>{{ __("Are you sure to approved all those request?") }}</p>
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                <button type="submit" class="btn btn--base btn-loading ">{{ __("Approved") }}</button>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="integratedModal" tabindex="-1" >
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="integratedModalLabel">
                <h5 class="modal-title">{{ __("Set All Transactions as integrated in CBS") }} </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.money.out.integrated.all') }}" method="POST">

                    @csrf
                    @method("GET")
                    <div class="row mb-10-none">
                        <div class="col-xl-12 col-lg-12 form-group">
                           <p>{{ __("Are you sure to set as integrated all these transaction?") }}</p>
                        </div>
                    </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                <button type="submit" class="btn btn--base btn-loading ">{{ __("Approved") }}</button>
            </div>
        </form>
        </div>
    </div>
</div>

<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="integratedModalLabel">
                <h5 class="modal-title">{{ __("Selectionner l'agent et la date") }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.money.out.export.cbspending') }}" method="POST">
                    @csrf
                    @method("POST")

                    {{-- Agent Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group">
                        <label for="agent_id">{{ __("Sélectionner l'agent") }} <span>*</span></label>
                        <select name="agent_id" id="agent_id" class="form--control select2-auto-tokenize" required>
                            <option disabled selected>{{ __("Select Agent") }}</option>
                            <option value="0">{{ __("Tous les agents") }}</option>
                            @foreach ($agents ?? [] as $item)
                                <option value="{{ $item->id }}">{{ $item->firstname }} {{ $item->lastname }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group mt-3">
                        <label for="filter_date">{{ __("Date de collecte") }} <span>*</span></label>
                        <input type="date" name="filter_date" id="filter_date" class="form--control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base btn-loading">{{ __("Extract") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filterModalP" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="integratedModalLabel">
                <h5 class="modal-title">{{ __("Selectionner l'agent et la date") }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.money.out.export.pending') }}" method="POST">
                    @csrf
                    @method("POST")

                    {{-- Agent Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group">
                        <label for="agent_id">{{ __("Sélectionner l'agent") }} <span>*</span></label>
                        <select name="agent_id" id="agent_id" class="form--control select2-auto-tokenize" required>
                            <option disabled selected>{{ __("Select Agent") }}</option>
                            <option value="0">{{ __("Tous les agents") }}</option>
                            @foreach ($agents ?? [] as $item)
                                <option value="{{ $item->id }}">{{ $item->firstname }} {{ $item->lastname }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group mt-3">
                        <label for="filter_date">{{ __("Date de collecte") }} <span>*</span></label>
                        <input type="date" name="filter_date" id="filter_date" class="form--control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base btn-loading">{{ __("Extract") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filterModalV" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="integratedModalLabel">
                <h5 class="modal-title">{{ __("Selectionner l'agent et la date") }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.money.out.export.validated') }}" method="POST">
                    @csrf
                    @method("POST")

                    {{-- Agent Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group">
                        <label for="agent_id">{{ __("Sélectionner l'agent") }} <span>*</span></label>
                        <select name="agent_id" id="agent_id" class="form--control select2-auto-tokenize" required>
                            <option disabled selected>{{ __("Select Agent") }}</option>
                            <option value="0">{{ __("Tous les agents") }}</option>
                            @foreach ($agents ?? [] as $item)
                                <option value="{{ $item->id }}">{{ $item->firstname }} {{ $item->lastname }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group mt-3">
                        <label for="filter_date">{{ __("Date de collecte") }} <span>*</span></label>
                        <input type="date" name="filter_date" id="filter_date" class="form--control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base btn-loading">{{ __("Extract") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filterModalC" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="integratedModalLabel">
                <h5 class="modal-title">{{ __("Selectionner l'agent et la date") }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.money.out.export.rejected') }}" method="POST">
                    @csrf
                    @method("POST")

                    {{-- Agent Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group">
                        <label for="agent_id">{{ __("Sélectionner l'agent") }} <span>*</span></label>
                        <select name="agent_id" id="agent_id" class="form--control select2-auto-tokenize" required>
                            <option disabled selected>{{ __("Select Agent") }}</option>
                            <option value="0">{{ __("Tous les agents") }}</option>
                            @foreach ($agents ?? [] as $item)
                                <option value="{{ $item->id }}">{{ $item->firstname }} {{ $item->lastname }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group mt-3">
                        <label for="filter_date">{{ __("Date de collecte") }} <span>*</span></label>
                        <input type="date" name="filter_date" id="filter_date" class="form--control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base btn-loading">{{ __("Extract") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filterModalCC" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="integratedModalLabel">
                <h5 class="modal-title">{{ __("Selectionner l'agent et la date") }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.money.out.export.integrated') }}" method="POST">
                    @csrf
                    @method("POST")

                    {{-- Agent Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group">
                        <label for="agent_id">{{ __("Sélectionner l'agent") }} <span>*</span></label>
                        <select name="agent_id" id="agent_id" class="form--control select2-auto-tokenize" required>
                            <option disabled selected>{{ __("Select Agent") }}</option>
                            <option value="0">{{ __("Tous les agents") }}</option>
                            @foreach ($agents ?? [] as $item)
                                <option value="{{ $item->id }}">{{ $item->firstname }} {{ $item->lastname }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group mt-3">
                        <label for="filter_date">{{ __("Date de collecte") }} <span>*</span></label>
                        <input type="date" name="filter_date" id="filter_date" class="form--control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base btn-loading">{{ __("Extract") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filterModalCE" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="integratedModalLabel">
                <h5 class="modal-title">{{ __("Selectionner l'agent et la date") }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.money.out.export.data') }}" method="POST">
                    @csrf
                    @method("POST")

                    {{-- Agent Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group">
                        <label for="agent_id">{{ __("Sélectionner l'agent") }} <span>*</span></label>
                        <select name="agent_id" id="agent_id" class="form--control select2-auto-tokenize" required>
                            <option disabled selected>{{ __("Select Agent") }}</option>
                            <option value="0">{{ __("Tous les agents") }}</option>
                            @foreach ($agents ?? [] as $item)
                                <option value="{{ $item->id }}">{{ $item->firstname }} {{ $item->lastname }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date Selector --}}
                    <div class="col-xl-12 col-lg-12 form-group mt-3">
                        <label for="filter_date">{{ __("Date de collecte") }} <span>*</span></label>
                        <input type="date" name="filter_date" id="filter_date" class="form--control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="modal-footer mt-4">
                        <button type="button" class="btn btn--danger" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                        <button type="submit" class="btn btn--base btn-loading">{{ __("Extract") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('script')
<script>
    (function ($) {
       "use strict";
        $('.approvedBtn').on('click', function () {
            var modal = $('#approvedModal');
            modal.modal('show');
        });
        $('.integratedBtn').on('click', function () {
            var modal = $('#integratedModal');
            modal.modal('show');
        });
        $('.filterBtn').on('click', function () {
            var modal = $('#filterModal');
            modal.modal('show');
        });
        $('.filterBtnP').on('click', function () {
            var modal = $('#filterModalP');
            modal.modal('show');
        });
        $('.filterBtnV').on('click', function () {
            var modal = $('#filterModalV');
            modal.modal('show');
        });
        $('.filterBtnC').on('click', function () {
            var modal = $('#filterModalC');
            modal.modal('show');
        });
        $('.filterBtnCC').on('click', function () {
            var modal = $('#filterModalCC');
            modal.modal('show');
        });
        $('.filterBtnCE').on('click', function () {
            var modal = $('#filterModalCE');
            modal.modal('show');
        });
   })(jQuery);
</script>
@endpush
