<?php

namespace App\Events;

use App\Models\Randevu;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RandevuDurumuDegisti
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Randevu $randevu,
        public string $eskiDurum,
        public string $yeniDurum,
    ) {}
}
