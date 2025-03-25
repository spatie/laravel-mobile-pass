<?php

namespace Spatie\LaravelMobilePass\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelMobilePass\Models\Concerns\HasMobilePasses;

class TestModel extends Model
{
    use HasMobilePasses;
}
