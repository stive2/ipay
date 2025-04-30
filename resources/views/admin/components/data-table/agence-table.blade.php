<table class="custom-table currency-search-table">
    <thead>
        <tr>
            <th>{{ __("Code") }}</th>
            <th>{{ __("Name") }}</th>
            <th>{{ __("Country") }}</th>
            <th>{{ __("City") }}</th>
            <th>{{__("action")}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($agences ?? [] as $item)
            <tr data-item="{{ $item->editData }}">
                <td><span>{{ $item->code }}</span>
                </td>
                <td>{{ $item->name }}
                    @if ($item->default)
                        <span class="badge badge--success ms-1">{{ __("Default") }}</span>
                    @endif
                </td>
                <td>{{ $item->country }}</td>
                <td>{{ $item->city }}</td>
                <td>
                    @include('admin.components.link.edit-default',[
                        'href'          => "javascript:void(0)",
                        'class'         => "edit-modal-button",
                        'permission'    => "admin.agence.update",
                    ])
                    @if (!$item->isDefault())
                        @include('admin.components.link.delete-default',[
                            'href'          => "javascript:void(0)",
                            'class'         => "delete-modal-button",
                            'permission'    => "admin.agence.delete",
                        ])
                    @endif
                </td>
            </tr>
        @empty
            @include('admin.components.alerts.empty',['colspan' => 7])
        @endforelse
    </tbody>
</table>

@push("script")
    <script>
        $(document).ready(function(){
            // Switcher
            switcherAjax("{{ setRoute('admin.agence.status.update') }}");
        })
    </script>
@endpush
