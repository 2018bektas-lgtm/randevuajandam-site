<?php

namespace App\Events;

use App\Models\Randevu;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RandevuOlusturuldu
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Randevu $randevu,
    ) {}
}
