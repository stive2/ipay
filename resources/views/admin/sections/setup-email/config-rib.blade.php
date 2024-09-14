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
    ], 'active' => __("Setup RIB verification")])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __("RIB Method verification") }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" method="POST" action="{{ setRoute('admin.setup.rib.config.update') }}">
                @csrf
                @method("PUT")
                <div class="row mb-10-none">
                    <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.input',[
                            'label'     => __("URL*"),
                            'name'      => 'url',
                            'value'     => old('url',$rib_config->url ?? ""),
                        ])
                    </div>
                    <div class="col-xl-6 col-lg-6 form-group">
                        @include('admin.components.form.input',[
                            'label'     => __("Token*"),
                            'name'      => 'token',
                            'value'     => old('token',$rib_config->token ?? ""),
                        ])
                    </div>
                    <div class="col-xl-6 col-lg-6 form-group">
                        <label>{{ __("RIB verification active ?*") }}</label>
                        <select class="form--control nice-select" name="active">
                            <option disabled selected>{{ __("select Method") }}</option>
                            <option value="yes" @if (isset($rib_config->active) && $rib_config->active == "yes")
                                @selected(true)
                            @endif>YES</option>
                            <option value="no">NO</option>
                        </select>
                        @error("active")
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.button.form-btn',[
                            'class'         => "w-100 btn-loading",
                            'text'          => __("update"),
                            'permission'    => "admin.setup.rib.config.update",
                        ])
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('script')

@endpush
