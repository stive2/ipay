<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\SmsNotification;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SMSNotificationExport implements FromArray, WithHeadings
{

    public function headings(): array
    {
        return [
            ['ID', 'RECIPIENT', 'SENDER ID', 'TYPE', 'MESSAGE CONTENT', 'STATUS', 'RESPONSE MESSAGE', 'TIME'],
        ];
    }

    public function array(): array
    {
        return SmsNotification::latest()->get()->map(function ($item, $key) {
            return [
                'id'    => $key + 1,
                'recipient'   => $item->recipient,
                'sender_id'   => $item->sender_id,
                'type'   => $item->type,
                'message'   => $item->message,
                'recstatusipient'   => $item->status,
                'response'   => $item->response,
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
        })->toArray();
    }
}
