<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Illuminate\Validation\ValidationException;

class InvalidPass extends ValidationException implements MobilePassException {}
