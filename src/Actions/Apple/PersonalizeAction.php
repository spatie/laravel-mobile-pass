<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Spatie\LaravelMobilePass\Events\PassPersonalized;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Apple\PersonalizationTokenSigner;

class PersonalizeAction
{
    /** @param  array<string, mixed>  $submittedInfo */
    public function execute(MobilePass $pass, string $personalizationToken, array $submittedInfo): string
    {
        $pass->personalization()->updateOrCreate([], [
            'personalization_token' => $personalizationToken,
            'submitted_info' => $submittedInfo,
            'personalized_at' => now(),
        ]);

        $pass->touch();

        event(new PassPersonalized($pass, $submittedInfo));

        return (new PersonalizationTokenSigner)->sign($personalizationToken);
    }
}
