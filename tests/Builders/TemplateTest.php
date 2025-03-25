<?php

use Spatie\LaravelMobilePass\Entities\Colour;
use Spatie\LaravelMobilePass\Entities\Image;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Models\Template;

it('inherits background colour from template', function () {
    $template = Template::make()
        ->setBackgroundColour(
            Colour::makeFromHex('#330033')
        );

    $template->save();

    $pass = MobilePass::factory()
        ->make()
        ->setIconImage(
            Image::make(
                getTestSupportPath('images/spatie-thumbnail.png')
            )
        );

    $pass->save();
    $pass->template()->associate($template);

    expect($pass->generate())->toMatchMobilePassSnapshot();
});

// it('triggers change for all passes using template when template is modified', function () {
//     $template = Template::make()
//         ->setBackgroundColour(
//             Colour::makeFromHex('#330033')
//         );

//     $template->save();

//     $passes = MobilePass::factory()
//         ->make()
//         ->times(3)
//         ->has($template)
//         ->setIconImage(
//             Image::make(
//                 getTestSupportPath('images/spatie-thumbnail.png')
//             )
//         );

//     $pass->save();
//     $pass->template()->associate($template);

//     expect($pass->generate())->toMatchMobilePassSnapshot();
// });
