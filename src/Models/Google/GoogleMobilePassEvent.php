<?php

namespace Spatie\LaravelMobilePass\Models\Google;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

/**
 * @property string $id
 * @property string $mobile_pass_id
 * @property string $event_type
 * @property Carbon $received_at
 * @property array<string, mixed>|null $raw_payload
 * @property MobilePass $mobilePass
 */
class GoogleMobilePassEvent extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'mobile_pass_google_events';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'json',
            'received_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<MobilePass, $this> */
    public function mobilePass(): BelongsTo
    {
        return $this->belongsTo(Config::mobilePassModel(), 'mobile_pass_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeSaves(Builder $query): Builder
    {
        return $query->where('event_type', 'save');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeRemoves(Builder $query): Builder
    {
        return $query->where('event_type', 'remove');
    }
}
