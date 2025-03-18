<?php

namespace Spatie\LaravelMobilePass\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registration extends Model
{
    use SoftDeletes;

    public $table = 'mobile_pass_registrations';

    public $fillable = [
        'device_id',
        'pass_type_id',
        'pass_serial',
        'push_token',
    ];

    public function pass(): BelongsTo
    {
        return $this->belongsTo(MobilePass::class, 'id', 'pass_serial');
    }
}
