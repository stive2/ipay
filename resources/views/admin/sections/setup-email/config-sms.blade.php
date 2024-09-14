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
    ], 'active' => __("Setup SMS")])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __("SMS Method") }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" method="POST" action="{{ setRoute('admin.setup.sms.config.update') }}">
                @csrf
                @method("PUT")
                <div class="row mb-10-none">
                    <div class="col-xl-10 col-lg-10 form-group">
                        @include('admin.components.form.input',[
                            'label'     => __("URL*"),
                            'name'      => 'url',
                            'value'     => old('url',$sms_config->url ?? ""),
                        ])
                    </div>
                    <div class="col-xl-2 col-lg-2">
                        <div class="row align-items-end">
                            <div class="col-xl-12 col-lg-12 form-group">
                                <!-- Open Modal For Test SMS Send -->
                                @include('admin.components.link.custom',[
                                    'class'         => "btn--base modal-btn w-100",
                                    'href'          => "#test-sms",
                                    'text'          => __("Send test SMS"),
                                    'permission'    => "admin.setup.sms.test.sms.send",
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.input',[
                            'label'     => __("Token*"),
                            'name'      => 'token',
                            'value'     => old('token',$sms_config->token ?? ""),
                        ])
                    </div>
                    <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.input',[
                            'label'     => __("Sender ID*"),
                            'name'      => 'sender_id',
                            'value'     => old('sender_id',$sms_config->sender_id ?? ""),
                        ])
                    </div>
                    {{-- <div class="col-xl-6 col-lg-6 form-group" id="show_hide_password"> --}}
                    {{--  <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.email-input-password',[
                            'label'         => __("password"),
                            'placeholder'   => __("password"),
                            'name'          => 'password',
                            'value'         => old('password',$email_config->password ?? ""),
                        ])
                    </div>  --}}
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

    {{-- Test sms send modal --}}
    @include('admin.components.modals.send-text-sms')

@endsection

@push('script')

@endpush
