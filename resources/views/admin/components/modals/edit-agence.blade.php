@if (admin_permission_by_name("admin.agence.update"))
    <div id="agence-edit" class="mfp-hide large">
        <div class="modal-data">
            <div class="modal-header px-0">
                <h5 class="modal-title">{{ __("Edit Agence") }}</h5>
            </div>
            <div class="modal-form-data">
                <form class="modal-form" method="POST" action="{{ setRoute('admin.agence.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method("PUT")
                    @include('admin.components.form.hidden-input',[
                        'name'          => 'target',
                        'value'         => old('target'),
                    ])
                    <div class="row mb-10-none">
                        <div class="col-xl-6 col-lg-6 form-group">
                            <label>{{ __("country") }}*</label>
                            <select name="agence_country" class="form--control select2-auto-tokenize country-select" data-old="{{ old('agence_country') }}">
                                <option selected disabled>Select Country</option>
                            </select>
                        </div>
                        <div class="col-xl-6 col-lg-6 form-group">
                            @include('admin.components.form.input',[
                                'label'         => __('City').'*',
                                'name'          => 'agence_city',
                                'value'         => old('agence_city'),
                            ])
                        </div>
                        <div class="col-xl-6 col-lg-6 form-group">
                            @include('admin.components.form.input',[
                                'label'         => __('name').'*',
                                'name'          => 'agence_name',
                                'value'         => old('agence_name'),
                            ])
                        </div>
                        <div class="col-xl-6 col-lg-6 form-group">
                            @include('admin.components.form.input',[
                                'label'         => __('Code').'*',
                                'name'          => 'agence_code',
                                'value'         => old('agence_code'),
                            ])
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group">
                            @include('admin.components.form.switcher',[
                                'label'         => 'Option*',
                                'name'          => 'agence_option',
                                'value'         => old('agence_option'),
                                'options'       => ['Optional' => 0,'Default' => 1],
                            ])
                        </div>

                        <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                            <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                            <button type="submit" class="btn btn--base">{{ __("update") }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push("script")
        <script>
            $(document).ready(function(){
                reloadAllCountries("select[name=agence_country]");
                openModalWhenError("agence_edit","#agence-edit");
                $(document).on("click",".edit-modal-button",function(){
                    var oldData = JSON.parse($(this).parents("tr").attr("data-item"));
                    var editModal = $("#agence-edit");

                    editModal.find(".invalid-feedback").remove();
                    editModal.find(".form--control").removeClass("is-invalid");

                    editModal.find("form").first().find("input[name=target]").val(oldData.code);
                    editModal.find("input[name=agence_code]").val(oldData.code);
                    editModal.find("input[name=agence_name]").val(oldData.name);
                    editModal.find("input[name=agence_city]").val(oldData.city);
                    editModal.find("input[name=agence_symbol]").val(oldData.symbol);
                    editModal.find("input[name=agence_option]").val(oldData.option);
                    editModal.find(".selcted-agence-edit").text(oldData.code);
                    editModal.find("select[name=agence_country]").attr("data-old",oldData.country);

                    reloadAllCountries("select[name=agence_country]");
                    refreshSwitchers("#agence-edit");
                    openModalBySelector("#agence-edit");

                });
            });
        </script>
    @endpush
@endif
