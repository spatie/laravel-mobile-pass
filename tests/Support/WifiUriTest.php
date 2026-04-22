<?php

use Spatie\LaravelMobilePass\Support\WifiUri;

it('builds a WPA uri with ssid and password', function () {
    expect(WifiUri::build('Spatie Guest', 'welcome'))
        ->toBe('WIFI:S:Spatie Guest;T:WPA;P:welcome;;');
});

it('builds a nopass uri when no password is given', function () {
    expect(WifiUri::build('Open Network'))
        ->toBe('WIFI:S:Open Network;T:nopass;;');
});

it('builds a nopass uri when password is empty', function () {
    expect(WifiUri::build('Open Network', ''))
        ->toBe('WIFI:S:Open Network;T:nopass;;');
});

it('marks the network as hidden', function () {
    expect(WifiUri::build('Hidden', 'secret', hidden: true))
        ->toBe('WIFI:S:Hidden;T:WPA;P:secret;H:true;;');
});

it('escapes reserved characters in ssid and password', function () {
    expect(WifiUri::build('a;b,c:d"e\\f', 'p;q'))
        ->toBe('WIFI:S:a\;b\,c\:d\"e\\\\f;T:WPA;P:p\;q;;');
});
