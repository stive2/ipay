@extends('agent.layouts.user_auth')

@php
    $lang = selectedLang();
    $auth_slug = Illuminate\Support\Str::slug(App\Constants\SiteSectionConst::AUTH_SECTION);
    $auth_text = App\Models\Admin\SiteSections::getData( $auth_slug)->first();
    $type =  Illuminate\Support\Str::slug(App\Constants\GlobalConst::USEFUL_LINKS);
    $policies = App\Models\Admin\SetupPage::orderBy('id')->where('type', $type)->where('slug',"terms-and-conditions")->where('status',1)->first();
@endphp

@section('content')
<section class="account">
    <div class="account-area">
        <div class="account-wrapper">
            <div class="account-logo text-center">
               <a class="site-logo" href="{{ setRoute('index') }}">
                <img src="{{ get_logo_agent($basic_settings) }}"  data-white_img="{{ get_logo_agent($basic_settings,'white') }}"
                data-dark_img="{{ get_logo_agent($basic_settings,'dark') }}"
                    alt="site-logo">
               </a>
            </div>
            <h5 class="title">Enregistrer un agent de collecte</h5>
            {{--  <p>{{ __(@$auth_text->value->language->$lang->register_text) }}</p>  --}}
            <form class="account-form" action="{{ route('agent.send.code') }}" method="POST">
                @csrf
                <div class="row ml-b-20">
                    {{--  <div class="col-xl-12 col-lg-12 form-group">
                        @include('admin.components.form.switcher',[
                            'label'         => 'ID*',
                            'name'          => 'type',
                            'value'         => old('type','tel'),
                            'options'       => ['Email' => 'email', 'Tel' => 'tel'],
                        ])
                    </div>  --}}
                    <div class="col-xl-12 col-lg-12 form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text copytext"><span>{{ __("ID") }}</span></span>
                            </div>
                            <input name="id" class="form--control  " placeholder="Entrer l'adresse mail ou le téléphone unique" required value="{{ old('id') }}">

                        </div>
                        <small class="text-danger exits"></small>
                    </div>

                    @if($basic_settings->agent_agree_policy)
                    <div class="col-lg-12 form-group">
                        <div class="custom-check-group">
                            <input type="checkbox" id="agree" name="agree" required>
                            <label for="agree">{{ __("I have agreed with") }} <a href=" {{  $policies != null? setRoute('useful.link',$policies->slug):"javascript:void(0)" }}">{{__("Terms Of Use & Privacy Policy")}}</a></label>
                        </div>
                    </div>
                    @endif
                    <div class="col-lg-12 form-group text-center">
                        <button type="submit"  class="btn--base w-100  btn-loading   ">{{ __("Continue") }} </button>
                    </div>
                    <div class="col-lg-12 text-center">
                        <div class="account-item">
                            <label><a href="{{ setRoute('admin.dashboard') }}" class="account-control-btn">{{ __("Retourner au dashboard") }}</a></label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<!--~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    End acount
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~-->

<ul class="bg-bubbles">
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
    <li></li>
</ul>
@endsection

@push('script')
@endpush
