<div class="custom-card mb-10">
    <div class="card-header">
        <h6 class="title">{{ __($title) ?? "" }}</h6>
    </div>
    <div class="card-body">
        {{--  <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 form-group">
            @include('admin.components.form.switcher',[
                'label'         => __( "Commission Type"),
                'name'          => 'type_commission',
                'value'         => old('type_commission',$item->type_commission),
               'options'       => [__("Fixed Commission") => 0, __("Tiered Commission") => 1],
                'onload'        => true,
                'data_target'   =>$item->id,
            ])
        </div>  --}}
        <form class="card-form" method="POST" action="{{ $route ?? "" }}">
            @csrf
            @method("PUT")

            <input type="hidden" value="{{ $item->slug }}" name="slug">
            <div class="row">
                <div class="{{ $item->agent_profit == true ? 'col-xl-4 col-lg-4': 'col-xl-6 col-lg-6 '}} mb-10">
                    <div class="custom-inner-card">
                        <div class="card-inner-header">
                            <h5 class="title">{{ __("Range") }}</h5>
                        </div>
                        <div class="card-inner-body">
                            <div class="row">
                                <div class="col-xxl-12 col-xl-6 col-lg-6  form-group">
                                    <label>{{ __("Minimum amount") }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" value="{{ old($data->slug.'_min_limit',$data->min_limit) }}" name="{{$data->slug}}_min_limit">
                                        <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                    </div>
                                </div>
                                <div class="col-xxl-12 col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Maximum amount") }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" value="{{ old($data->slug.'_max_limit',$data->max_limit) }}" name="{{$data->slug}}_max_limit">
                                        <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="{{ $item->agent_profit == true ? 'col-xl-4 col-lg-4': 'col-xl-6 col-lg-6 '}} mb-10">
                    <div class="custom-inner-card">
                        <div class="card-inner-header">
                            <h5 class="title">{{ __("Charges") }}</h5>
                        </div>
                        <div class="card-inner-body">
                            <div class="row">
                                <div class="col-xxl-12 col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Fixed Charge*") }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" value="{{ old($data->slug.'_fixed_charge',$data->fixed_charge) }}" name="{{$data->slug}}_fixed_charge">
                                        <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                    </div>
                                </div>
                                <div class="col-xxl-12 col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Percent Charge*") }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" value="{{ old($data->slug.'_percent_charge',$data->percent_charge) }}" name="{{$data->slug}}_percent_charge">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if( $item->agent_profit == true)
                <div class="col-xl-4 col-lg-4 mb-10">
                    <div class="custom-inner-card">
                        <div class="card-inner-header">
                            <h5 class="title-agent">{{ __("Agent Profits") }}</h5>
                        </div>
                        <div class="card-inner-body">
                            <div class="row">
                                <div class="col-xxl-12 col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Fixed Commissions") }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" value="{{ old($data->slug.'_agent_fixed_commissions',$data->agent_fixed_commissions) }}" name="{{$data->slug}}_agent_fixed_commissions">
                                        <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                    </div>
                                </div>
                                <div class="col-xxl-12 col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Percent Commissions") }}</label>
                                    <div class="input-group">
                                        <input type="text" class="form--control number-input" value="{{ old($data->slug.'_agent_percent_commissions',$data->agent_percent_commissions) }}" name="{{$data->slug}}_agent_percent_commissions">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            {{--  @foreach ($item->paliers_commissions as $palier)
                <h6 class="title">{{ __($palier['title']) ?? "" }}</h6>
                <div class="row">
                    <div class="{{ $palier['agent_profit'] == true ? 'col-xl-4 col-lg-4': 'col-xl-6 col-lg-6 '}} mb-10">
                        <div class="custom-inner-card">
                            <div class="card-inner-header">
                                <h5 class="title">{{ __("Range") }}</h5>
                            </div>
                            <div class="card-inner-body">
                                <div class="row">
                                    <div class="col-xxl-12 col-xl-6 col-lg-6  form-group">
                                        <label>{{ __("Minimum amount") }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form--control number-input" value="{{ old('min_limit_'.$palier['id'],$palier['min_limit_'.$palier['id']]) }}" name="min_limit_{{$palier['id']}}">
                                            <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("Maximum amount") }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form--control number-input" value="{{ old('max_limit_'.$palier['id'],$palier['max_limit_'.$palier['id']]) }}" name="max_limit_{{$palier['id']}}">
                                            <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="{{ $palier['agent_profit'] == true ? 'col-xl-4 col-lg-4': 'col-xl-6 col-lg-6 '}} mb-10">
                        <div class="custom-inner-card">
                            <div class="card-inner-header">
                                <h5 class="title">{{ __("Charges") }}</h5>
                            </div>
                            <div class="card-inner-body">
                                <div class="row">
                                    <div class="col-xxl-12 col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("Fixed Charge*") }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form--control number-input" value="{{ old('fixed_charge_'.$palier['id'],$palier['fixed_charge_'.$palier['id']]) }}" name="{{$data->slug}}_fixed_charge">
                                            <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("Percent Charge*") }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form--control number-input" value="{{ old('percent_charge_'.$palier['id'],$palier['percent_charge_'.$palier['id']]) }}" name="{{$data->slug}}_percent_charge">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if( $palier['agent_profit'] == true)
                    <div class="col-xl-4 col-lg-4 mb-10">
                        <div class="custom-inner-card">
                            <div class="card-inner-header">
                                <h5 class="title-agent">{{ __("Agent Profits") }}</h5>
                            </div>
                            <div class="card-inner-body">
                                <div class="row">
                                    <div class="col-xxl-12 col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("Fixed Commissions") }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form--control number-input" value="{{ old('agent_fixed_commissions_'.$palier['id'],$palier['agent_fixed_commissions_'.$palier['id']]) }}" name="{{$data->slug}}_agent_fixed_commissions">
                                            <span class="input-group-text">{{ get_default_currency_code($default_currency) }}</span>
                                        </div>
                                    </div>
                                    <div class="col-xxl-12 col-xl-6 col-lg-6 form-group">
                                        <label>{{ __("Percent Commissions") }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form--control number-input" value="{{ old('agent_percent_commissions_'.$palier['id'],$palier['agent_percent_commissions_'.$palier['id']]) }}" name="{{$data->slug}}_agent_percent_commissions">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            @endforeach  --}}
            <div class="row mb-10-none">
                <div class="col-xl-12 col-lg-12 form-group">
                    @include('admin.components.button.form-btn',[
                        'text'          => "update",
                        'class'         => "w-100 btn-loading",
                        'permission'    => "admin.trx.settings.charges.update",
                    ])
                </div>
            </div>
        </form>
    </div>
</div>

@push('script')
<script>
     $(document).ready(function(){
        switcherAjax("{{ setRoute('user.virtual.card.change.status') }}");
    })

</script>
@endpush
