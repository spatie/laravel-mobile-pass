<?php

namespace Spatie\LaravelMobilePass\Models\Apple;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

/**
 * @property string $mobile_pass_id
 * @property string $description
 * @property array $required_fields
 * @property ?string $terms_and_conditions
 * @property ?string $personalization_token
 * @property ?array $submitted_info
 * @property ?\Illuminate\Support\Carbon $personalized_at
 * @property MobilePass $pass
 */
class AppleMobilePassPersonalization extends Model
{
    use HasFactory;
    use HasUuids;

    public $guarded = [];

    public function pass(): BelongsTo
    {
        return $this->belongsTo(Config::mobilePassModel(), 'mobile_pass_id');
    }

    protected function casts(): array
    {
        return [
            'required_fields' => 'json',
            'submitted_info' => 'json',
            'personalized_at' => 'datetime',
        ];
    }
}
