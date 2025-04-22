<table class="custom-table agent-search-table">
    <thead>
        <tr>
            <th>{{ __("Nom") }}</th>
            <th>{{ __("Prénom") }}</th>
            <th>{{ __("Email") }}</th>
            <th>{{ __("Matricule") }}</th>
            <th>{{ __("Phone") }}</th>
            @if (Route::currentRouteName() == "admin.agents.index" || Route::currentRouteName() == "admin.agents.active" || Route::currentRouteName() == "admin.agents.locate")
                <th>{{ __("Solde") }}</th>
            @endif
            @if (Route::currentRouteName() == "admin.agents.index" || Route::currentRouteName() == "admin.agents.active" || Route::currentRouteName() == "admin.agents.locate")
                <th>{{ __("Commissions") }}</th>
            @endif
            <th>{{__("Status") }}</th>
            @if (Route::currentRouteName() == "admin.agents.active")
                <th>{{__("First MDP") }}</th>
            @endif
            <th>{{__("Collecte") }}</th>
            <th>{{__("action")}}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($agents ?? [] as $key => $item)
            <tr>
                <td><span>{{ $item->firstname }}</span></td>
                <td><span>{{ $item->lastname }}</span></td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->matricule }}</td>
                <td>{{ $item->full_mobile }}</td>
                @if (Route::currentRouteName() == "admin.agents.index" || Route::currentRouteName() == "admin.agents.active" || Route::currentRouteName() == "admin.agents.locate")
                    <td>{{ get_amount($item->wallet->balance,get_default_currency_code())}}</td>
                @endif
                @if (Route::currentRouteName() == "admin.agents.index" || Route::currentRouteName() == "admin.agents.active" || Route::currentRouteName() == "admin.agents.locate")
                    <td>{{ get_amount($item->commissions,get_default_currency_code())}}</td>
                @endif
                <td>
                    @if (Route::currentRouteName() == "admin.agents.kyc.unverified")
                        <span class="{{ $item->kycStringStatus->class }}">{{ __($item->kycStringStatus->value ) }}</span>
                    @else
                        <span class="{{ $item->stringStatus->class }}">{{ __($item->stringStatus->value) }}</span>
                    @endif
                </td>
                <td>
                    @if (Route::currentRouteName() == "admin.agents.active" && strlen($item->remember_token) == 8)
                        {{ $item->remember_token }}
                    @endif
                </td>
                <td>
                    @if ($item->status)
                        @if ($item->collecte_on)
                            @include('admin.components.link.info-default',[
                                'href'          => setRoute('admin.web.settings.close.collect.agent', $item->id),
                                'text'          => __("Fermer"),
                                'class'         => "bg--success",
                                // 'permission'    => "admin.agents.details",
                            ]) ON
                        @else
                            @include('admin.components.link.info-default',[
                                'href'          => setRoute('admin.web.settings.open.collect.agent', $item->id),
                                'class'         => "bg--danger",
                                'text'          => __("Ouvrir"),
                                // 'permission'    => "admin.agents.details",
                            ]) OFF
                        @endif
                    @endif
                </td>
                <td>
                    @if (Route::currentRouteName() == "admin.agents.kyc.unverified")
                        @include('admin.components.link.info-default',[
                            'href'          => setRoute('admin.agents.kyc.details', $item->username),
                            'permission'    => "admin.agents.kyc.details",
                        ])
                    @elseif(Route::currentRouteName() == "admin.agents.locate")
                        @if ($item->latestCoordinate != null)
                            <button class="btn btn-primary localiser-btn"
                                data-user-id="{{ $item->id }}"
                                data-latitude="{{ $item->latestCoordinate->latitude }}"
                                data-longitude="{{ $item->latestCoordinate->longitude }}"
                                data-user-name="{{ $item->firstname." ".$item->lastname }}"
                            >
                            Locate
                            </button>
                        @endif
                    @else
                        @include('admin.components.link.info-default',[
                            'href'          => setRoute('admin.agents.details', $item->username),
                            'permission'    => "admin.agents.details",
                        ])
                    @endif
                </td>
            </tr>
        @empty
            @include('admin.components.alerts.empty',['colspan' => 7])
        @endforelse
    </tbody>
</table>
