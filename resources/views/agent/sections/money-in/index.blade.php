@extends('agent.layouts.master')

@push('css')

@endpush

@section('breadcrumb')
    @include('agent.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("agent.dashboard"),
        ]
    ], 'active' => __(@$page_title)])
@endsection

@section('content')
<div class="body-wrapper">
    <div class="dashboard-area mt-10">
        <div class="dashboard-header-wrapper">
            <h3 class="title">{{__(@$page_title)}}</h3>
        </div>
    </div>
    <div class="row mb-30-none">
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __(@$page_title) }}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <form class="card-form">
                            @csrf
                            <div class="row">
                                <div class="col-xl-12 col-lg-12 form-group text-center">
                                    <div class="exchange-area">
                                        <code class="d-block text-center"><span class="fees-show">--</span> <span class="limit-show">--</span></code>
                                    </div>
                                </div>
                                <div class="col-xxl-12 col-xl-12 col-lg-12 form-group paste-wrapper">
                                    <label>{{ __("ID") }} ({{ __("User") }})<span class="text--base">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text copytext"><span>{{ __("ID") }}</span></span>
                                        </div>
                                        <input name="email" class="form--control checkUser" id="username" placeholder="{{ __('enter user ID') }}"/>
                                    </div>
                                    {{--  <button type="button" class="paste-badge scan"  data-toggle="tooltip" title="Scan QR"><i class="fas fa-camera"></i></button>  --}}
                                    <label class="exist text-start"></label>

                                </div>

                                <div class="col-xxl-12 col-xl-12 col-lg-12 form-group">
                                    <label>{{ __("Amount") }}<span>*</span></label>
                                    <div class="input-group">
                                        <input type="number" min="0" step="0.01" class="form--control number-input" required placeholder="{{ __('enter Amount') }}" name="amount">

                                        <select class="form--control nice-select currency currency-select" name="currency">
                                            <option disabled selected>{{ __("Select User Wallet") }}</option>
                                            @foreach (all_currencies() ?? [] as $item)
                                                <option
                                                    value="{{ $item->id }}"
                                                    name="{{ $item->name }}"
                                                    code="{{ $item->code }}"
                                                    symbol="{{ $item->symbol }}"
                                                >
                                                    {{ $item->code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <code class="d-block mt-10 text-end text--warning balance-show">{{ __("Available Balance") }} {{ authWalletBalance() }} {{ get_default_currency_code() }}</code>
                                </div>

                                <div class="col-xl-12 col-lg-12">

                                    {{--  <button class="wallet-balance-update-btn btn--base w-100">{{ __("Confirm Send") }} <i class="fas fa-paper-plane ms-1"></i></i></button>  --}}
                                    <div class="user-action-btn">
                                        @include('admin.components.button.custom',[
                                            'type'          => "button",
                                            'class'         => "wallet-balance-update-btn btn--base w-100 btn-loading",
                                            'text'          => __("Collect"),
                                            'icon'          => "fas fa-paper-plane ms-1",
                                        ])
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="dash-payment-item-wrapper">
                <div class="dash-payment-item active">
                    <div class="dash-payment-title-area">
                        <span class="dash-payment-badge">!</span>
                        <h5 class="title">{{ __(@$page_title) }} {{__("Preview")}}</h5>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-coins"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Entered Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fw-bold request-amount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Transfer Fee") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fees">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Recipient Received") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="recipient-get">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{__("Total Payable")}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="last payable-total text-warning">--</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-list-area mt-20">
        <div class="dashboard-header-wrapper">
            <h4 class="title ">{{__("Money In Log")}}</h4>
            <div class="dashboard-btn-wrapper">
                <div class="dashboard-btn mb-2">
                    <a href="{{ setRoute('agent.transactions.index','money-in') }}" class="btn--base">{{__("View More")}}</a>
                </div>
            </div>
        </div>
        <div class="dashboard-list-wrapper">
            @include('agent.components.transaction-log',compact("transactions"))
        </div>
    </div>
</div>
<div class="modal fade" id="scanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
            <div class="modal-body text-center">
                <video id="preview" class="p-1 border" style="width:300px;"></video>
            </div>
            <div class="modal-footer justify-content-center">
              <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">@lang('close')</button>
            </div>
      </div>
    </div>
</div>
<div id="wallet-balance-update-modal" class="mfp-hide large">
    <div class="modal-data">
        <div class="modal-header px-0">
            <h5 class="modal-title">{{ __("Confirm") }}</h5>
        </div>
        <div class="modal-form-data">
            <form class="modal-form" method="POST" action="{{ setRoute('agent.money.in.confirmed') }}">
                @csrf
                <div class="row mb-10-none">
                    <div class="col-xl-12 col-lg-12 form-group paste-wrapper">
                        <label>{{ __("ID") }} ({{ __("User") }})<span class="text--base">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text copytext"><span>{{ __("ID") }}</span></span>
                            </div>
                            <input readonly name="email" class="form--control checkUser utilisateur" id="user" placeholder="{{ __('enter user ID') }}" />
                        </div>
                        {{--  <button type="button" class="paste-badge scan"  data-toggle="tooltip" title="Scan QR"><i class="fas fa-camera"></i></button>  --}}
                        <label class="exist text-start"></label>

                    </div>

                    <div class="col-xl-12 col-lg-12 form-group">
                        <label>{{ __("Amount") }}<span>*</span></label>
                        <div class="input-group">
                            <input readonly type="text" class="form--control number-input montant" required placeholder="{{__('enter Amount')}}" name="amount" id="montant">
                            <select class="form--control nice-select currency" name="currency">
                                {{--  <option value="{{ get_default_currency_code() }}">{{ get_default_currency_code() }}</option>  --}}
                                <option disabled selected>{{ __("Select User Wallet") }}</option>
                                  @foreach (all_currencies() ?? [] as $item)
                                      <option value="{{ $item->id }}">{{ $item->code }}</option>
                                  @endforeach
                            </select>
                        </div>
                        <code class="d-block mt-10 text-end text--warning balance-show">{{ __("Available Balance") }} {{ authWalletBalance() }} {{ get_default_currency_code() }}</code>
                    </div>
                    <div class="dash-payment-body">
                        <div class="preview-list-wrapper">

                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-coins"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Entered Amount") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fw-bold request-amount">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-battery-half"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Transfer Fee") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="fees">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-receipt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{ __("Recipient Received") }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="recipient-get">--</span>
                                </div>
                            </div>
                            <div class="preview-list-item">
                                <div class="preview-list-left">
                                    <div class="preview-list-user-wrapper">
                                        <div class="preview-list-user-icon">
                                            <i class="las la-money-check-alt"></i>
                                        </div>
                                        <div class="preview-list-user-content">
                                            <span>{{__("Total Payable")}}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="preview-list-right">
                                    <span class="last payable-total text-warning">--</span>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        <label>Mettre à jour le numéro de téléphone du client<span class="text--base">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text copytext"><span>{{ __("Téléphone") }}</span></span>
                            </div>
                            <input required class="form-control form--control" name="mobile" id="mobile" placeholder="{{ __('Téléphone') }}">
                        </div>
                    </div>
                    {{--  <div class="col-xl-12 col-lg-12 form-group">
                        <label>Mettre à jour le mail du client<span class="text--base">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text copytext"><span>{{ __("Email") }}</span></span>
                            </div>
                            <input type="email" required class="form-control form--control" name="email" id="email" placeholder="{{ __('Email') }}">
                        </div>
                    </div>  --}}
                    <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                        <button type="button" class="btn btn--danger modal-close">{{ __("closeS") }}</button>
                        <button type="submit" class="btn btn--base">{{__("Confirmer")}}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
<script>
//  'use strict'
    (function ($) {
        $('.scan').click(function(){
            var scanner = new Instascan.Scanner({ video: document.getElementById('preview'), scanPeriod: 5, mirror: false });
            scanner.addListener('scan',function(content){
                var route = '{{url('agent/user/qr/scan/')}}'+'/'+content
                $.get(route, function( data ) {
                    if(data.error){
                        // alert(data.error)
                        throwMessage('error',[data.error]);
                    } else {
                        $("#username").val(data);
                        $("#username").focus()
                    }
                    $('#scanModal').modal('hide')
                });
            });

            Instascan.Camera.getCameras().then(function (cameras){
                if(cameras.length>0){
                    $('#scanModal').modal('show')
                        scanner.start(cameras[0]);
                } else{
                //    alert('No cameras found.');
                    throwMessage('error',["No camera found "]);
                }
            }).catch(function(e){
                // alert('No cameras found.');
                throwMessage('error',["No camera found "]);
            });
        });
        $('.checkUser').on('keyup',function(e){
            var url = '{{ route('agent.money.in.check.exist') }}';
            var value = $(this).val();
            var token = '{{ csrf_token() }}';
            if ($(this).attr('name') == 'email') {
                var data = {email:value,_token:token}

            }
            $.post(url,data,function(response) {
                if(response.own){
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exist').addClass('text--danger').text(response.own);
                    $('.transfer').attr('disabled',true)
                    return false
                }
                if(response['data'] != null){
                    if($('.exist').hasClass('text--danger')){
                        $('.exist').removeClass('text--danger');
                    }
                    $('.exist').text(response['data']['firstname'] + ' ' + response['data']['lastname']).addClass('text--success');
                    $('#mobile').val(response['data']['mobile']);
                    $('#email').val(response['data']['email']);
                    $('.transfer').attr('disabled',false)
                } else {
                    if($('.exist').hasClass('text--success')){
                        $('.exist').removeClass('text--success');
                    }
                    $('.exist').text('User doesn\'t  exists.').addClass('text--danger');
                    $('.transfer').attr('disabled',true)
                    return false
                }

            });
        });
    })(jQuery);
</script>
<script>
     var defualCurrency = "{{ get_default_currency_code() }}";
     var defualCurrencyRate = "{{ get_default_currency_rate() }}";

        $(document).ready(function(){
            $(document).on("change",".currency-select",function() {
                var selectedValue = $(this).find(":selected");
                var currencyName = selectedValue.attr("name");
                var currencyCode = selectedValue.attr("code");
                var currencySymbol = selectedValue.attr("symbol");

                $("input[name=currency_name]").val(currencyName);
                $("input[name=currency_code]").val(currencyCode);
                $("input[name=currency_symbol]").val(currencySymbol);

                getLimit();
                getFees();
                getPreview(currencyCode);
            });

            getLimit();
            getFees();
            getPreview();
        });

        $("input[name=amount]").keyup(function(){
             getFees();
             getPreview();
        });

        $(".wallet-balance-update-btn").click(function(){
            $('input[id=user]').val($("input[name=email]").val());
            $('input[id=montant]').val($("input[name=amount]").val());
            if($("input[name=email]").val() && $("input[name=amount]").val()) {
                openModalBySelector("#wallet-balance-update-modal");
            }

        });

        function openModalBySelector(selector,animation = "mfp-move-horizontal") {
            $(selector).addClass("white-popup mfp-with-anim");
            if(animation == null) {
              animation = "mfp-zoom-in"
            }
            $.magnificPopup.open({
              removalDelay: 500,
              items: {
                src: $(selector), // can be a HTML string, jQuery object, or CSS selector
                type: 'inline',
              },

              callbacks: {
                beforeOpen: function() {
                  this.st.mainClass = animation;
                },
                elementParse: function(item) {
                  var modalCloseBtn = $(selector).find(".modal-close");
                  $(modalCloseBtn).click(function() {
                    $.magnificPopup.close();
                  });
                },
              },
            });
            $.magnificPopup.instance._onFocusIn = function(e) {
              // Do nothing if target element is select2 input
              if( $(e.target).hasClass('select2-search__field') ) {
                  return true;
              }
              // Else call parent method
              $.magnificPopup.proto._onFocusIn.call(this,e);
            }
          }
        function getLimit() {
            var currencyCode = acceptVar().currencyCode;
            var currencyRate = acceptVar().currencyRate;

            var min_limit = acceptVar().currencyMinAmount;
            var max_limit =acceptVar().currencyMaxAmount;
            if($.isNumeric(min_limit) || $.isNumeric(max_limit)) {
                var min_limit_calc = parseFloat(min_limit/currencyRate).toFixed(2);
                var max_limit_clac = parseFloat(max_limit/currencyRate).toFixed(2);
                $('.limit-show').html("{{ __('limit') }} " + min_limit_calc + " " + currencyCode + " - " + max_limit_clac + " " + currencyCode);

                return {
                    minLimit:min_limit_calc,
                    maxLimit:max_limit_clac,
                };
            }else {
                $('.limit-show').html("--");
                return {
                    minLimit:0,
                    maxLimit:0,
                };
            }
        }
        function acceptVar() {
            var selectedVal = $(this).find(":selected");
            var currencyCode = selectedVal.attr("code");
            // var selectedVal = $("select[name=currency] :selected");
            // var currencyCode = $("select[name=currency] :selected").val();
            var currencyRate = defualCurrencyRate;
            var currencyMinAmount ="{{getAmount($moneyInCharge->min_limit)}}"
            var currencyMaxAmount = "{{getAmount($moneyInCharge->max_limit)}}"
            var currencyFixedCharge = "{{getAmount($moneyInCharge->fixed_charge)}}"
            var currencyPercentCharge = "{{getAmount($moneyInCharge->percent_charge)}}"

            return {
                currencyCode:currencyCode,
                currencyRate:currencyRate,
                currencyMinAmount:currencyMinAmount,
                currencyMaxAmount:currencyMaxAmount,
                currencyFixedCharge:currencyFixedCharge,
                currencyPercentCharge:currencyPercentCharge,
                selectedVal:selectedVal,

            };
        }
        function feesCalculation() {
            var currencyCode = acceptVar().currencyCode;
            var currencyRate = acceptVar().currencyRate;
            var sender_amount = $("input[name=amount]").val();
            sender_amount == "" ? (sender_amount = 0) : (sender_amount = sender_amount);

            var fixed_charge = acceptVar().currencyFixedCharge;
            var percent_charge = acceptVar().currencyPercentCharge;
            if ($.isNumeric(percent_charge) && $.isNumeric(fixed_charge) && $.isNumeric(sender_amount)) {
                // Process Calculation
                var fixed_charge_calc = parseFloat(currencyRate * fixed_charge);
                var percent_charge_calc = parseFloat(currencyRate)*(parseFloat(sender_amount) / 100) * parseFloat(percent_charge);
                var total_charge = parseFloat(fixed_charge_calc) + parseFloat(percent_charge_calc);
                total_charge = parseFloat(total_charge).toFixed(2);
                // return total_charge;
                return {
                    total: total_charge,
                    fixed: fixed_charge_calc,
                    percent: percent_charge,
                };
            } else {
                // return "--";
                return false;
            }
        }

        function getFees() {
            var currencyCode = acceptVar().currencyCode;
            var percent = acceptVar().currencyPercentCharge;
            var charges = feesCalculation();
            if (charges == false) {
                return false;
            }
            $(".fees-show").html("{{ __('Transfer Fee') }} " + parseFloat(charges.fixed).toFixed(2) + " " + currencyCode + " + " + parseFloat(charges.percent).toFixed(2) + "%  ");
        }
        function getPreview(currencyCode) {
                var senderAmount = $("input[name=amount]").val();
                var sender_currency = currencyCode;
                var sender_currency_rate = acceptVar().currencyRate;
                senderAmount == "" ? senderAmount = 0 : senderAmount = senderAmount;
                // Sending Amount
                $('.request-amount').text(senderAmount + " " + sender_currency);

                // Fees
                var charges = feesCalculation();
                var total_charge = 0;
                if(senderAmount == 0){
                    total_charge = 0;
                }else{
                    total_charge = charges.total;
                }

                $('.fees').text(total_charge + " " + sender_currency);
                // // recipient received
                var recipient = (parseFloat(senderAmount) - parseFloat(total_charge)) * parseFloat(sender_currency_rate)
                var recipient_get = 0;
                if(senderAmount == 0){
                     recipient_get = 0;
                }else{
                     recipient_get =  parseFloat(recipient);
                }
                $('.recipient-get').text(parseFloat(recipient_get).toFixed(2) + " " + sender_currency);

                 // Pay In Total
                var totalPay = parseFloat(senderAmount) * parseFloat(sender_currency_rate)
                var pay_in_total = 0;
                if(senderAmount == 0){
                     pay_in_total = 0;
                }else{
                     pay_in_total =  parseFloat(totalPay); // + parseFloat(charges.total);
                }
                $('.payable-total').text(parseFloat(pay_in_total).toFixed(2) + " " + sender_currency);

        }

</script>

{{--  <script>
    const defualCurrency = @json(get_default_currency_code());
    const defualCurrencyRate = @json(get_default_currency_rate());
    const moneyInCharge = {
        min_limit: parseFloat("{{ getAmount($moneyInCharge->min_limit) }}"),
        max_limit: parseFloat("{{ getAmount($moneyInCharge->max_limit) }}"),
        fixed_charge: parseFloat("{{ getAmount($moneyInCharge->fixed_charge) }}"),
        percent_charge: parseFloat("{{ getAmount($moneyInCharge->percent_charge) }}")
    };

    $(document).ready(function () {
        $(".currency-select").on("change", function () {
            const selected = $(this).find(":selected");
            const currencyName = selected.attr("name");
            const currencyCode = selected.attr("code");
            const currencySymbol = selected.attr("symbol");

            $("input[name=currency_name]").val(currencyName);
            $("input[name=currency_code]").val(currencyCode);
            $("input[name=currency_symbol]").val(currencySymbol);

            updateFeesAndPreview();
        });

        $("input[name=amount]").on("keyup change", function () {
            updateFeesAndPreview();
        });

        $(".wallet-balance-update-btn").on("click", function () {
            $('#user').val($("input[name=email]").val());
            $('#montant').val($("input[name=amount]").val());

            if ($("input[name=email]").val() && $("input[name=amount]").val()) {
                openModalBySelector("#wallet-balance-update-modal");
            }
        });

        $("#show_hide_password a").on('click', function (e) {
            e.preventDefault();
            const $input = $('#show_hide_password input');
            const $icon = $('#show_hide_password i');
            const type = $input.attr("type") === "text" ? "password" : "text";
            $input.attr('type', type);
            $icon.toggleClass("fa-eye fa-eye-slash");
        });

        updateFeesAndPreview();
    });

    function acceptVar() {
        const selected = $("select[name=currency] :selected");
        return {
            currencyCode: selected.attr("code") || defualCurrency,
            currencyRate: parseFloat(defualCurrencyRate),
            currencyMinAmount: moneyInCharge.min_limit,
            currencyMaxAmount: moneyInCharge.max_limit,
            currencyFixedCharge: moneyInCharge.fixed_charge,
            currencyPercentCharge: moneyInCharge.percent_charge,
        };
    }

    function updateFeesAndPreview() {
        updateLimitDisplay();
        updateFeeDisplay();
        updatePreview();
    }

    function updateLimitDisplay() {
        const { currencyCode, currencyRate, currencyMinAmount, currencyMaxAmount } = acceptVar();

        if ($.isNumeric(currencyMinAmount) && $.isNumeric(currencyMaxAmount)) {
            const min = (currencyMinAmount / currencyRate).toFixed(2);
            const max = (currencyMaxAmount / currencyRate).toFixed(2);
            $('.limit-show').html(`{{ __('limit') }} ${min} ${currencyCode} - ${max} ${currencyCode}`);
        } else {
            $('.limit-show').html("--");
        }
    }

    function calculateFees() {
        const { currencyRate, currencyFixedCharge, currencyPercentCharge } = acceptVar();
        let senderAmount = parseFloat($("input[name=amount]").val()) || 0;

        const fixed = currencyFixedCharge * currencyRate;
        const percent = (senderAmount * percent_charge * currencyRate) / 100;
        const total = (fixed + percent).toFixed(2);

        return { total, fixed, percent: currencyPercentCharge };
    }

    function updateFeeDisplay() {
        const { currencyCode } = acceptVar();
        const charges = calculateFees();
        $(".fees-show").html(`{{ __('Transfer Fee') }} ${charges.fixed.toFixed(2)} ${currencyCode} + ${parseFloat(charges.percent).toFixed(2)}%`);
    }

    function updatePreview() {
        const { currencyRate, currencyCode } = acceptVar();
        const amount = parseFloat($("input[name=amount]").val()) || 0;
        const charges = calculateFees();

        $('.request-amount').text(`${amount} ${defualCurrency}`);
        $('.fees').text(`${charges.total} ${currencyCode}`);

        const recipientAmount = ((amount - charges.total) * currencyRate).toFixed(2);
        $('.recipient-get').text(`${recipientAmount} ${currencyCode}`);

        const totalPay = (amount * currencyRate).toFixed(2);
        $('.payable-total').text(`${totalPay} ${currencyCode}`);
    }

    function openModalBySelector(selector, animation = "mfp-move-horizontal") {
        $(selector).addClass("white-popup mfp-with-anim");
        $.magnificPopup.open({
            removalDelay: 500,
            items: { src: $(selector), type: 'inline' },
            callbacks: {
                beforeOpen: function () {
                    this.st.mainClass = animation;
                },
                elementParse: function () {
                    $(selector).find(".modal-close").click(function () {
                        $.magnificPopup.close();
                    });
                }
            }
        });
        $.magnificPopup.instance._onFocusIn = function (e) {
            if ($(e.target).hasClass('select2-search__field')) return true;
            $.magnificPopup.proto._onFocusIn.call(this, e);
        }
    }
</script>  --}}
@endpush
