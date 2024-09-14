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
    ], 'active' => __("Fees & Charges")])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __("Commissions Settings") }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" method="POST" action="{{ setRoute('admin.trx.settings.mincommission.update') }}">
                @csrf
                @method("PUT")
                <div class="row mb-10-none">
                    <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.input',[
                            'label'     => __("Minimum payable commission per month*"),
                            'name'      => 'min_commission_payable',
                            'type'      => 'number',
                            'value'     => old('min_commission_payable',$basic_settings->min_commission_payable ?? ""),
                        ])
                    </div>
                    <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.input',[
                            'label'     => __("Minimum amount to collect per month*"),
                            'name'      => 'min_collect_amount',
                            'type'      => 'number',
                            'value'     => old('min_collect_amount',$basic_settings->min_collect_amount ?? ""),
                        ])
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.button.form-btn',[
                            'class'         => "w-100 btn-loading",
                            'text'          => __("update"),
                            'permission'    => "admin.setup.sms.config.update",
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>
    @foreach ($transaction_charges as $item)
        @if($item->status == 1)
            @include('admin.components.trx-settings-charge-block',[
                'route'         => setRoute('admin.trx.settings.charges.update'),
                'title'         => $item->title,
                'data'          => $item,
            ])
        @endif
    @endforeach
@endsection

@push('script')

@endpush
