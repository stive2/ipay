<div class="row">
    <div class="col-md-3">
        <h4 class="title">Total perçu : <span style="color: green">{{ get_amount($percus,get_default_currency_code()) }}</span></h4>
    </div>
    <div class="col-md-3">
        <h4 class="title">Total non perçu : <span style="color: red">{{ get_amount($npercus,get_default_currency_code()) }}</span></h4>
    </div>
    <div class="col-md-3">
        <h4 class="title">Total commissions : <span style="color: primary">{{ get_amount($total,get_default_currency_code()) }}</span></h4>
    </div>
    <div class="col-md-3" style="text-align: right">
        @include('admin.components.link.custom',[
            'text'          => __("Exporter"),
            'icon'          => "fas fa-download me-1",
            // 'permission'    => "admin.agents.store",
            'class'         => "btn--base py-2 px-4 bg--primary",
            'href'          => setRoute('agent.profits.exportData'),
        ])
    </div>
</div>
<table class="custom-table">
    <thead>
        <tr>
            <th>{{ __("Ref. Transaction") }}</th>
            {{--  <th>{{ __("Transaction Type") }}</th>  --}}
            <th>{{ __("Montant de la collecte") }}</th>
            <th>{{ __("Montant de la commission") }}</th>
            <th>{{ __("Status") }}</th>
            <th>{{ __("Date") }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($profits?? [] as $item)
            <tr>
                <td>{{ $item->transactions->trx_id }}</td>
                {{--  <td>{{$item->transactions->type }}</td>  --}}
                <td>{{ get_amount($item->transactions->details->charges->conversion_amount??$item->transactions->details->charges->sender_amount,$item->transactions->details->charges->wallet_currencyy??get_default_currency_code())}}</td>
                <td>{{ get_amount($item->total_charge,$item->transactions->details->charges->wallet_currency??get_default_currency_code())}}</td>
                <td>
                    @if ($item->paid == '0')
                        <span style="color: red">Non perçu</span>
                    @else
                        <span style="color: green">Perçu</span>
                    @endif
                </td>
                <td>{{ $item->created_at->format("d-m-Y h:i") }}</td>
            </tr>
        @empty
        @include('admin.components.alerts.empty',['colspan' => 5])
        @endforelse
    </tbody>
</table>
@push('script')

@endpush
