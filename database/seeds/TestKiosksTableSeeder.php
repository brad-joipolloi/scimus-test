<?php

use Illuminate\Database\Seeder;

class TestKiosksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create a registered, but not setup kiosk
        $kiosk = factory(App\Kiosk::class)->make([
            'name' => null,
            'location' => null,
            'asset_tag' => null,
            'identifier' => 'test-kiosk-one',
        ]);

        $kiosk->package()->associate(\App\Package::whereName('default')->first());
        $kiosk->save();

        // Create a few kiosks that are fully set up
        factory(App\Kiosk::class, 4)->create();
    }
}