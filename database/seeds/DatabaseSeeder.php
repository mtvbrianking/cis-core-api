<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ModulesTableSeeder::class);
        $this->call(FacilitiesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(OauthClientsTableSeeder::class);

<<<<<<< HEAD
=======
        $this->call(ProductTableSeeder::class);
        $this->call(StoresTableSeeder::class);
        $this->call(BatchesTableSeeder::class);
        $this->call(InventoryTableSeeder::class);

>>>>>>> patients
        $this->call(PharmacyModuleSeeder::class);
    }
}
