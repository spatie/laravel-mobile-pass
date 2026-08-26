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
        $personalization = $pass->personalization()->firstOrFail();

        $signature = (new PersonalizationTokenSigner)->sign($personalizationToken);

        $personalization->update([
            'personalization_token' => $personalizationToken,
            'submitted_info' => $submittedInfo,
            'personalized_at' => now(),
        ]);

        $pass->touch();

        event(new PassPersonalized($pass, $submittedInfo));

        return $signature;
    }
}
