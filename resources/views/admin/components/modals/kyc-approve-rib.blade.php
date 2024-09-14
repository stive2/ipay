@if (admin_permission_by_name("admin.users.kyc.approve"))
    @isset($user)
        @if ($user->kyc_verified != global_const()::VERIFIED)
            {{-- KYC Approved Modal RIB --}}
            <div id="aproved-modal" class="mfp-hide large">
                <div class="modal-data">
                    <div class="modal-header px-0">
                        <h5 class="modal-title">RIB {{ "@" . $user->username }}</h5>
                    </div>
                    <div class="modal-form-data">
                        <form class="modal-form" method="POST" action="{{ setRoute('admin.users.kyc.approve',$user->username) }}" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="target" value="{{ $user->username }}">
                            <div class="row mb-10-none">
                                <div class="col-xl-6 col-lg-6 form-group">
                                        @include('admin.components.form.input',[
                                            'label'     => "RIB of user*",
                                            'name'      => "rib",
                                            'value'     => old("rib"),
                                        ])
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    @include('admin.components.form.input',[
                                        'label'     => "Confirm RIB of user*",
                                        'name'      => "rib_confirmation",
                                        'value'     => old("rib_confirmation"),
                                    ])
                                </div>

                                <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                                    <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                                    <button type="submit" class="btn btn--base">{{ __("Submit") }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        @push("script")
            <script>
                $(".approve-btn").click(function(){
                    openModalBySelector($("#aproved-modal"))
                });
            </script>
        @endpush
    @endisset
@endif
