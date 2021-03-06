<?php

namespace Tests\Feature\KioskClient;

use Tests\ActsAs;
use Tests\ResetsDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use ActsAs, ResetsDatabase;

    public function testRegistrationFromRegisteredKioskReturnsUnprocessableEntity()
    {
        $response = $this->actingAsRegisteredKiosk()
            ->postJson('/api/kiosk/register', [
                'identifier' => $this->kioskIdentifier,
                'client' => [
                    'version' => '1.0.0',
                ],
            ])
        ;

        $response->assertStatus(422)
            ->assertJson([
                'message' => true,
                'errors' => [
                    'identifier' => true,
                ],
            ])
        ;
    }

    public function testRegistrationFromUnregisteredKioskReturnsRegistrationConfirmation()
    {
        $response = $this->actingAsUnregisteredKiosk()
            ->postJson('/api/kiosk/register', [
                'identifier' => $this->kioskIdentifier,
                'client' => [
                    'version' => '1.0.0',
                ],
            ])
        ;

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => null,
                    'location' => null,
                    'asset_tag' => null,
                    'identifier' => $this->kioskIdentifier,
                    'client_version' => '1.0.0',
                    'assigned_package_version' => null,
                    'current_package_version' => null,
                ],
            ])
            ->assertJsonStructure([
                'data' => [
                    'name',
                    'location',
                    'asset_tag',
                    'identifier',
                    'client_version',
                    'assigned_package_version',
                    'current_package_version',
                    'last_seen_at',
                ]
            ])
        ;
    }
}
