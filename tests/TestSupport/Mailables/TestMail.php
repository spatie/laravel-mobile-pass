<?php

namespace Spatie\LaravelMobilePass\Tests\TestSupport\Mailables;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Assert as PHPUnit;
use Spatie\LaravelMobilePass\Models\MobilePass;
use function Laravel\Prompts\text;

class TestMail extends Mailable
{
    public function __construct(protected MobilePass $pass)
    {

    }

    public function envelope()
    {
        return new Envelope(
            from: 'john@example.com',
            to: 'jane@example.com',
            subject: 'Test email',
        );
    }

    public function content()
    {
        return new Content(
            htmlString: 'This is a test email',
        );
    }

    public function attachments()
    {
        return [$this->pass];
    }

    public function assertHasAttachedFileName($name, array $options = [])
    {
        $this->renderForAssertions();

        PHPUnit::assertTrue(
            (new Collection($this->rawAttachments))->contains(
                function ($attachment) use ($name, $options) {
                    return $attachment['data'] !== null
                        && $attachment['name'] === $name
                        && array_filter($attachment['options']) === array_filter($options);
                }
            ),
            'Did not find an attachment with the expected name.'
        );


        return $this;
    }
}
