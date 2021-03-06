<?php

use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TestUsersTableSeeder::class);
        $this->call(TestKiosksTableSeeder::class);
        $this->call(CustomPagesModelSeeder::class);
    }
}
