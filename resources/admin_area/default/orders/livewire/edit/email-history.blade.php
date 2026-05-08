<?php

use Livewire\Volt\Component;
use App\Models\Email;
use Illuminate\Support\Str;

new class extends Component
{
    public $order;
    public $emails;

    public function mount($order)
    {
        $this->order = $order;
        $orderViewPath = parse_url(route('orders.view', ['order' => $this->order->id]), PHP_URL_PATH);

        $this->emails = Email::query()
            ->where('display', 1)
            ->where(function ($query) use ($orderViewPath) {
                $query->where(function ($nested) {
                    $nested->where('mailable_type', get_class($this->order))
                        ->where('mailable_id', $this->order->id);
                })->orWhere(function ($nested) use ($orderViewPath) {
                    $nested->where('user_id', $this->order->user_id)
                        ->whereNotNull('button_url')
                        ->where('button_url', 'like', '%' . $orderViewPath . '%');
                });
            })
            ->latest()
            ->get();
    }
}

?>

<div>
    {{--  Email History Table  --}}
    @livewire(admin_view_path('livewire.table'), [
        'title' => __('messages.email_history'),
        'class' => '',
        'entries' => 15,
        'columns' => [
            __('messages.id'),
            __('messages.subject'),
            __('messages.to'),
            __('messages.status'),
            __('messages.created_at'),
            '',
        ],
        'sortableColumns' => [
            __('messages.id'),
            __('messages.subject'),
            __('messages.to'),
            __('messages.status'),
            __('messages.created_at'),
        ],
        'rows' => $emails->map(function ($email) {
            return [
                $email->id,
                Str::limit($email->subject, 50),
                $email->to,
                $email->status == 'delivered' ? '<span class="badge bg-green-lt">Delivered</span>' : ($email->status == 'read' ? '<span class="badge bg-info-lt">Read</span>' : ($email->status == 'failed' ? '<span class="badge bg-danger-lt">Failed</span>' : '<span class="badge bg-warning-lt">' . ucfirst($email->status) . '</span>')),
                $email->created_at->translatedFormat('d M Y H:i'),
                '<a href="' . route('admin.emails.view', $email->id) . '" target="_blank">' . __('messages.view') . '</a>'
            ];
        })->toArray(),
    ])
</div>
