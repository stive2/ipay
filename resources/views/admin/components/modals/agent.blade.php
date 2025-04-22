{{--  @if (admin_permission_by_name("admin.languages.import"))  --}}
    <div id="agent-import" class="mfp-hide medium">
        <div class="modal-data">
            <div class="modal-header px-0">
                <h5 class="modal-title">{{ __("Importer les agents de collecte") }}</h5>
            </div>
            <div class="modal-form-data">
                <form class="modal-form" method="POST" action="{{ setRoute('admin.agents.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row mb-10-none mt-2">
                        <div class="col-xl-12 col-lg-12 form-group">
                            @include('admin.components.form.input-file',[
                                'label'     => __("Fichier des agents")." (.xlsx, .csv)*",
                                'name'      => "file",
                                'class'     => "form--control",
                                'accept'    => ".csv,.xlsx",
                                'attribute' => "style=height:auto",
                            ])
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                            <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                            <button type="submit" class="btn btn--base">{{ __("Importer") }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push("script")
        <script>
            openModalWhenError("agent-import","#agent-import");
        </script>
    @endpush
{{--  @endif  --}}
