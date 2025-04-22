<table class="custom-table user-search-table">
    <thead>
        <tr>
            <th></th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>{{ __("Matricule") }}</th>
            <th>Telephone</th>
            <th>N° de compte</th>
            <th>Type</th>
            {{--  <th>{{ __("email Verification") }}</th>  --}}
            <th>{{__("Status") }}</th>
            <th>{{__("action")}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($users ?? [] as $key => $item)
            <tr>
                <td>
                    <ul class="user-list">
                        <li><img src="{{ $item->userImage }}" alt="user"></li>
                    </ul>
                </td>
                <td><span>{{ $item->firstname }}</span></td>
                <td><span>{{ $item->lastname }}</span></td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->matricule }}</td>
                <td>{{ $item->full_mobile }}</td>
                <td>{{ $item->rib ?? "-" }}</td>
                <td>
                    @if ($item->transitional == "0")
                        Compte classic
                    @else
                        Compte de collecte
                    @endif
                </td>
                <td>
                    <span class="{{ $item->emailStatus->class }}">{{ __($item->emailStatus->value) }}</span>
                </td>
                {{--  <td>
                    @if (Route::currentRouteName() == "admin.users.kyc.unverified")
                        <span class="{{ $item->kycStringStatus->class }}">{{ __($item->kycStringStatus->value ) }}</span>
                    @else
                        <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span>
                    @endif
                </td>  --}}
                <td>
                    @if (Route::currentRouteName() == "admin.users.kyc.unverified")
                        @include('admin.components.link.info-default',[
                            'href'          => setRoute('admin.users.kyc.details', $item->username),
                            'permission'    => "admin.users.kyc.details",
                        ])
                    @else
                        @include('admin.components.link.info-default',[
                            'href'          => setRoute('admin.users.details', $item->username),
                            'permission'    => "admin.users.details",
                        ])
                    @endif
                </td>
            </tr>
        @empty
            @include('admin.components.alerts.empty',['colspan' => 7])
        @endforelse
    </tbody>
</table>
