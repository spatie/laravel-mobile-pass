<?php

namespace Spatie\LaravelMobilePass\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\LaravelMobilePass\Support\Config;

class MobilePassRegistration extends Model
{
    public $guarded = [];

    public function pass(): BelongsTo
    {
        $modelClass = Config::mobilePassModel();

        return $this->belongsTo($modelClass, 'id', 'pass_serial');
    }

    public function appleUpdateUrl(): string
    {
        return config('mobile-pass.apple.apple_push_base_url') . "/{$this->push_token}";
    }

    public function appleUpdatePayload(): array
    {
        return [];
    }
}
