<?php

namespace Spatie\LaravelMobilePass\Http\Controllers\Apple;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Actions\Apple\PersonalizeAction;
use Spatie\LaravelMobilePass\Http\Requests\Apple\PersonalizeRequest;
use Spatie\LaravelMobilePass\Support\Config;

/**
 * Implementing the Web Service (personalize endpoint)
 * Apple Wallet Developer Guide: Rewards Enrollment
 */
class PersonalizeController extends Controller
{
    public function __invoke(PersonalizeRequest $request): Response
    {
        /** @var class-string<PersonalizeAction> $actionClass */
        $actionClass = Config::getActionClass('personalize', PersonalizeAction::class);

        $signature = (new $actionClass)->execute(
            $request->mobilePass(),
            $request->input('personalizationToken'),
            $request->input('requiredPersonalizationInfo'),
        );

        return response($signature, 200, ['Content-Type' => 'application/octet-stream']);
    }
}
