<?php

namespace Tests\Feature;

use Tests\ActsAs;
use Tests\ResetsDatabase;
use Tests\TestCase;

class KioskClientApiTest extends TestCase
{
    use ActsAs, ResetsDatabase;

    public function testHealthCheckFromUnregisteredKioskReturnsEntityNotFound()
    {
        $response = $this->actingAsUnregisteredKiosk()
            ->postJson('/api/kiosk/health-check', [
                'identifier' => $this->kioskIdentifier,
                'client' => [
                    'version' => '1.0.0',
                ],
            ])
        ;

        $response->assertStatus(404);
    }

    public function testHealthCheckFromRegisteredKioskReturnsRequiredInformation()
    {
        $response = $this->actingAsRegisteredKiosk()
            ->postJson('/api/kiosk/health-check', [
                'identifier' => $this->kioskIdentifier,
                'client' => [
                    'version' => '1.0.0',
                ],
            ])
        ;

        $response->assertStatus(200)
            ->assertJson([

            ])
        ;
    }

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
                    'current_package' => null,
                    'last_seen_at' => true,
                    'package' => null,
                    'path' => true,
                ],
            ])
        ;
    }

    public function testAssigningAPackageToARegisteredKioskShowsOnTheKiosksNextHealthCheck()
    {
        $response = $this->actingAsAdmin()
            ->put('/api/kiosk/1/assign/1')
        ;

        $response->assertStatus(200);

        $response = $this->actingAsRegisteredKiosk()
            ->postJson('/api/kiosk/health-check', [
                'identifier' => $this->kioskIdentifier,
                'client' => [
                    'version' => '1.0.0',
                ],
            ])
        ;

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => null,
                    'location' => null,
                    'asset_tag' => null,
                    'identifier' => $this->kioskIdentifier,
                    'client_version' => '1.0.0',
                    'current_package' => null,
                    'last_seen_at' => true,
                    'package' => [
                        'name' => 'default',
                        'current_version' => [
                            'version' => '1',
                            'package_path' => true,
                        ],
                    ],
                    'path' => true,
                ],
            ])
        ;
    }

    public function testDownloadingAPackageAssignedToARegisteredKioskGivesAValidTarArchive()
    {
        $response = $this->actingAsAdmin()
            ->put('/api/kiosk/1/assign/1')
        ;

        $response->assertStatus(200);

        $response = $this->actingAsRegisteredKiosk()
            ->postJson('/api/kiosk/health-check', [
                'identifier' => $this->kioskIdentifier,
                'client' => [
                    'version' => '1.0.0',
                ],
            ])
        ;

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => null,
                    'location' => null,
                    'asset_tag' => null,
                    'identifier' => $this->kioskIdentifier,
                    'client_version' => '1.0.0',
                    'current_package' => null,
                    'last_seen_at' => true,
                    'package' => [
                        'name' => 'default',
                        'current_version' => [
                            'version' => '1',
                            'package_path' => true,
                        ],
                    ],
                    'path' => true,
                ],
            ])
        ;

        $response = $this->actingAsRegisteredKiosk()
            ->postJson('/api/kiosk/package-download', [
                'identifier' => $this->kioskIdentifier,
                'client' => [
                    'version' => '1.0.0',
                ],
            ])
        ;

        $response->assertStatus(200);

        $this->assertEquals('application/x-gzip', $response->headers->get('content-type'));
        $this->assertEquals('attachment; filename=default_1.package', $response->headers->get('content-disposition'));
    }
}