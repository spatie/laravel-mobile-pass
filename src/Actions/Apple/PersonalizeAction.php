<?php

namespace Spatie\LaravelMobilePass\Actions\Apple;

use Spatie\LaravelMobilePass\Events\PassPersonalized;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Apple\PersonalizationTokenSigner;
use Throwable;

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

        try {
            // touch() synchronously dispatches a push notification to every registered
            // device (via MobilePass::boot()'s `updated` listener). The signature has
            // already been produced and the submission already recorded above, so a push
            // failure here is a best-effort side effect, not part of the personalize
            // contract: it must not turn an otherwise-successful response into a 500,
            // which would leave the pass stuck in "personalized" state without Wallet
            // ever having received its valid signature.
            $pass->touch();
        } catch (Throwable $exception) {
            report($exception);
        }

        // Wallet may retry the /personalize POST, so listeners on PassPersonalized
        // should be written idempotently rather than assuming a single delivery per pass.
        event(new PassPersonalized($pass, $submittedInfo));

        return $signature;
    }
}
