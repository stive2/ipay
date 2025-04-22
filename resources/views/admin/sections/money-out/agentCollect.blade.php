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
                <div class="table-btn-area">
                    <button type="button" class="btn btn--base filterBtn">{{ __("Exporter") }}</button>
                </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __("Matricule") }}</th>
                        <th>{{ __("Agent") }}</th>
                        <th>{{ __("Téléphone") }}</th>
                        <th>{{ __("Email") }}</th>
                        <th>{{ __("Montant total collecté") }}</th>
                        {{--  <th>{{ __(("Status")) }}</th>  --}}
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions ?? []  as $key => $item)
                        <tr>
                            <td>
                                <a href="{{ setRoute('admin.agents.details',$item->username) }}">{{ $item->matricule }}</a>
                            </td>
                            <td>
                                <a href="{{ setRoute('admin.agents.details',$item->username) }}">{{ $item->firstname }} {{ $item->lastname }}</a>
                            </td>
                            <td>{{ $item->full_mobile }}</td>
                            <td>{{ $item->email }}</td>
                            <td style="text-align: right">{{ get_amount($item->montant,'XAF',2) }}</td>

                        </tr>
                    @empty
                         @include('admin.components.alerts.empty',['colspan' => 5])
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header p-3" id="integratedModalLabel">
                <h5 class="modal-title">{{ __("Selectionner la date ou laissez vide pour ne pas appliquer de filtre") }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form class="modal-form" action="{{ setRoute('admin.money.out.export.agentcollect') }}" method="POST">
                    @csrf
                    @method("POST")

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
        $('.filterBtn').on('click', function () {
            var modal = $('#filterModal');
            modal.modal('show');
        });
   })(jQuery);
</script>
@endpush
