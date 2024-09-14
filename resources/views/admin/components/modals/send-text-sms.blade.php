@if (admin_permission_by_name("admin.setup.sms.test.sms.send"))
    <div id="test-sms" class="mfp-hide medium">
        <div class="modal-data">
            <div class="modal-header px-0">
                <h5 class="modal-title">{{ __("Send Test SMS") }}</h5>
            </div>
            <div class="modal-form-data">
                <form class="modal-form" method="POST" action="{{ setRoute('admin.setup.sms.test.sms.send') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-10-none mt-3">
                        <div class="col-xl-12 col-lg-12 form-group">
                            @include('admin.components.form.input',[
                                'label'         =>__("Phone")."*",
                                'name'          => "recipient",
                                'type'          => "number",
                                'value'         => old("recipient"),
                            ])
                        </div>

                        <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                            <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                            <button type="submit" class="btn btn--base">{{ __("send") }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
