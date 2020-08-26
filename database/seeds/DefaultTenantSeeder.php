<?php

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DefaultTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::firstOrCreate(['id' => Tenant::DEFAULT_ID], ['name' => 'default']);
        $tenant->configuration ??= [];
        $tenant->configuration = $tenant->configuration + [
            'contact_address' => null,
            'contact_url' => null,
            'contact_zip' => null,
            'contact_city' => null,
            'contact_email' => null,
            'contact_phone' => null,
            'contact_state' => null,
            'logo1' => null,
            'logo2' => null,
            'display_name' => null,
            'favicon' => null,
            'title' => null,
            'primary_color' => null,
            'secondary_color' => null,
            'accent_color' => null,
        ];
        $tenant->save();
    }
}