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
    ], 'active' => __("SMS Sended Log")])
@endsection

@section('content')
<div class="table-area">
    <div class="table-wrapper">
        <div class="table-header">
            <h5 class="title">{{ $page_title }}</h5>
            @if(count($sms) > 0)
                <div class="table-btn-area">
                    <a href="{{ setRoute('admin.setup.sms.export.sms') }}" class="btn--base"><i class="fas fa-download me-1"></i>{{ __("Export Data") }}</a>
                </div>
            @endif
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>{{ __("ID") }}</th>
                        <th>{{ __("Recipient") }}</th>
                        <th>{{ __("Sender_id") }}</th>
                        <th>{{ __("Type") }}</th>
                        <th>{{ __("Message Content") }}</th>
                        <th>{{ __("Status") }}</th>
                        <th>{{ __("Response") }}</th>
                        <th>{{ __("Time") }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sms ?? []  as $key => $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->recipient }}</td>
                            <td>{{ $item->sender_id }}</td>
                            <td>{{ $item->type }}</td>
                            <td>{{ $item->message }}</td>
                            <td>{{ $item->status }}</td>
                            <td>{{ $item->response }}</td>
                            <td>{{ $item->created_at->format('d-m-y h:i:s A') }}</td>
                        </tr>
                    @empty
                         @include('admin.components.alerts.empty',['colspan' => 11])
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ get_paginate($sms) }}
    </div>
</div>
@endsection

@push('script')

@endpush
