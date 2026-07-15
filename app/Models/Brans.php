<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brans extends Model
{
    use HasFactory, HasSlug;

    protected $table = 'branslar';

    protected $fillable = [
        'ad',
        'slug',
    ];

    public function doktorlar()
    {
        return $this->belongsToMany(Doktor::class, 'doktor_brans', 'brans_id', 'doktor_id');
    }
}
