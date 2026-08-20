<?php

use Spatie\LaravelMobilePass\Builders\Apple\AirlinePassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Seat;
use Spatie\LaravelMobilePass\Enums\PassengerCapability;
use Spatie\LaravelMobilePass\Enums\TransitSecurityProgram;

it('builds a basic airline boarding pass', function () {
    $airlinePassBuilder = AirlinePassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->addHeaderField('flight-no', 'EY066', label: 'Flight')
        ->addHeaderField('seat', '66F')
        ->addField('departure', 'ABU', label: 'Abu Dhabi International')
        ->addField('destination', 'LHR', label: 'London Heathrow')
        ->addSecondaryField('name', 'Dan Johnson')
        ->addSecondaryField('gate', 'D68')
        ->addAuxiliaryField('departs', now()->toIso8601String())
        ->addAuxiliaryField('class', 'Economy')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))

        // Now set the semantic fields.
        ->setDepartureAirportCode('AUH')
        ->setDepartureAirportName('Abu Dhabi Intl')
        ->setDepartureLocationDescription('Abu Dhabi Intl')
        ->setDestinationAirportCode('LHR')
        ->setDestinationAirportName('London Heathrow')
        ->setDestinationLocationDescription('Abu Dhabi Intl')
        ->setSeats(Seat::make(
            number: '66F',
        ));

    $generatedPass = $airlinePassBuilder->generate();

    expect($generatedPass)->toMatchMobilePassSnapshot();

    // first save, model gets created
    $mobilePass = $airlinePassBuilder->save();

    // second save, model gets updated
    /** @var AirlinePassBuilder $rebuilder */
    $rebuilder = $mobilePass->builder();

    $rebuilder
        ->setSeats(Seat::make(number: '123DAN'))
        ->save();

    expect($mobilePass->generate())->toMatchMobilePassSnapshot();
});

it('keeps semantics that are falsy but meaningful', function () {
    $compiledData = AirlinePassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setSilenceRequested(false)
        ->setDuration(0)
        ->setInternationalDocumentsAreVerified(false)
        ->data();

    expect($compiledData['semantics'])->toMatchArray([
        'silenceRequested' => false,
        'duration' => 0,
        'internationalDocumentsAreVerified' => false,
    ]);
});

it('compiles general boarding semantics into the semantics payload', function () {
    $compiledData = builderWithEveryGeneralBoardingDetail()->data();

    expect($compiledData['semantics'])->toMatchArray(expectedGeneralBoardingSemantics());
});

it('omits general boarding semantics when none are set', function () {
    $compiledData = AirlinePassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->data();

    expect($compiledData)->not->toHaveKey('semantics');
});

it('round-trips general boarding semantics through save and hydrate', function () {
    $model = builderWithEveryGeneralBoardingDetail()->save();

    expect($model->builder()->data()['semantics'])->toMatchArray(expectedGeneralBoardingSemantics());
});

it('compiles airline semantics into the semantics payload', function () {
    $compiledData = builderWithEveryAirlineDetail()->data();

    expect($compiledData['semantics'])->toMatchArray(expectedAirlineSemantics());
});

it('round-trips airline semantics through save and hydrate', function () {
    $model = builderWithEveryAirlineDetail()->save();

    expect($model->builder()->data()['semantics'])->toMatchArray(expectedAirlineSemantics());
});

it('has a name', function () {
    expect(AirlinePassBuilder::name())->toBe('airline');
});

function builderWithEveryGeneralBoardingDetail(): AirlinePassBuilder
{
    return AirlinePassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setBoardingZone('A')
        ->setDepartureCityName('Abu Dhabi')
        ->setDestinationCityName('London')
        ->setMembershipProgramStatus('Gold')
        ->setTicketFareClass('Economy')
        ->setInternationalDocumentsAreVerified(true)
        ->setInternationalDocumentsVerifiedDeclarationName('Travel Ready')
        ->setDepartureLocationSecurityPrograms(TransitSecurityProgram::TsaPreCheck, TransitSecurityProgram::GlobalEntry)
        ->setDestinationLocationSecurityPrograms(TransitSecurityProgram::Clear)
        ->setPassengerEligibleSecurityPrograms(TransitSecurityProgram::TsaPreCheck)
        ->setDepartureLocationTimeZone('Asia/Dubai')
        ->setDestinationLocationTimeZone('Europe/London')
        ->setLoungePlaceIds('lounge-1', 'lounge-2');
}

function expectedGeneralBoardingSemantics(): array
{
    return [
        'boardingZone' => 'A',
        'departureCityName' => 'Abu Dhabi',
        'destinationCityName' => 'London',
        'membershipProgramStatus' => 'Gold',
        'ticketFareClass' => 'Economy',
        'internationalDocumentsAreVerified' => true,
        'internationalDocumentsVerifiedDeclarationName' => 'Travel Ready',
        'departureLocationSecurityPrograms' => ['PKTransitSecurityProgramTSAPreCheck', 'PKTransitSecurityProgramGlobalEntry'],
        'destinationLocationSecurityPrograms' => ['PKTransitSecurityProgramCLEAR'],
        'passengerEligibleSecurityPrograms' => ['PKTransitSecurityProgramTSAPreCheck'],
        'departureLocationTimeZone' => 'Asia/Dubai',
        'destinationLocationTimeZone' => 'Europe/London',
        'loungePlaceIDs' => ['lounge-1', 'lounge-2'],
    ];
}

function builderWithEveryAirlineDetail(): AirlinePassBuilder
{
    return AirlinePassBuilder::make()
        ->setOrganizationName('My organization')
        ->setSerialNumber(123456)
        ->setDescription('Hello!')
        ->setIconImage(getTestSupportPath('images/spatie-thumbnail.png'))
        ->setAirlineCode('EY')
        ->setFlightCode('EY066')
        ->setFlightNumber('66')
        ->setDepartureGate('D68')
        ->setDepartureTerminal('1')
        ->setDepartureAirportCode('AUH')
        ->setDepartureAirportName('Abu Dhabi Intl')
        ->setDestinationAirportCode('LHR')
        ->setDestinationAirportName('London Heathrow')
        ->setDestinationGate('A10')
        ->setDestinationTerminal('5')
        ->setPassengerAirlineSsrs('WCHR')
        ->setPassengerCapabilities(PassengerCapability::PriorityBoarding, PassengerCapability::Carryon)
        ->setPassengerInformationSsrs('VGML')
        ->setPassengerServiceSsrs('SPEQ');
}

function expectedAirlineSemantics(): array
{
    return [
        'airlineCode' => 'EY',
        'flightCode' => 'EY066',
        'flightNumber' => '66',
        'departureGate' => 'D68',
        'departureTerminal' => '1',
        'departureAirportCode' => 'AUH',
        'departureAirportName' => 'Abu Dhabi Intl',
        'destinationAirportCode' => 'LHR',
        'destinationAirportName' => 'London Heathrow',
        'destinationGate' => 'A10',
        'destinationTerminal' => '5',
        'passengerAirlineSSRs' => ['WCHR'],
        'passengerCapabilities' => ['PKPassengerCapabilityPriorityBoarding', 'PKPassengerCapabilityCarryon'],
        'passengerInformationSSRs' => ['VGML'],
        'passengerServiceSSRs' => ['SPEQ'],
    ];
}
