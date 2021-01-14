<?php

namespace Tests\Seeds;

use App\Models\Address;
use App\Models\TMSProvider;
use Illuminate\Database\Seeder;
use App\Models\CompanyAddressTMSCode;

class CargoWiseItgAddressesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $company = CompaniesSeeder::getTestItg();
        $tmsProvider = TMSProvider::getCargoWise();

        $baseAddress = [
            "code" => 'ANNPUBARB',
            "full_name" => 'ANN ARBOR PUBLIC SCHOOLS',
            "address" => '2555 S STATE ST',
            "address_2" => null,
            "city" => 'ANN ARBOR',
            "state" => 'MI',
            "post_code" => "48104",
        ];

        $address = Address::create([
            'address_line_1' => $baseAddress['address'],
            'address_line_2' => $baseAddress['address_2'],
            'city' => $baseAddress['city'],
            'state' => $baseAddress['state'],
            'postal_code' => $baseAddress['post_code'],
            'location_name' => $baseAddress['full_name'],
            'is_billable' => 0,
        ]);

        factory(CompanyAddressTMSCode::class)->create([
            't_address_id' => $address->id,
            't_company_id' => $company->id,
            't_tms_provider_id' => $tmsProvider->id,
            'company_address_tms_code' => $baseAddress['code'],
        ]);
    }
}
