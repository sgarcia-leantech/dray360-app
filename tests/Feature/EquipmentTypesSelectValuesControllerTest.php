<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Company;
use App\Models\TMSProvider;
use App\Models\EquipmentType;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EquipmentTypesSelectValuesControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_should_return_the_select_options_for_the_equipment_types_values()
    {
        $this->loginAdmin();
        $company = factory(Company::class)->create();
        $tmsProvider = factory(TMSProvider::class)->create();
        $equipmentTypes = factory(EquipmentType::class, 5)->create([
            't_company_id' => $company->id,
            't_tms_provider_id' => $tmsProvider->id,
        ]);

        $this->getJson(route('equipment-types-options.show', [
            'company' => $company->id,
            'tmsProvider' => $tmsProvider->id,
        ]))
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'equipment_types',
            'equipment_owners',
            'equipment_sizes',
            'scacs',
        ])
        ->assertJsonCount(
            $equipmentTypes->pluck('equipment_type')->unique()->count(),
            'equipment_types'
        )
        ->assertJsonCount(
            $equipmentTypes->pluck('equipment_owner')->unique()->count(),
            'equipment_owners'
        )
        ->assertJsonCount(
            $equipmentTypes->pluck('equipment_size')->unique()->count(),
            'equipment_sizes'
        )
        ->assertJsonCount(
            $equipmentTypes->pluck('scac')->unique()->count(),
            'scacs'
        );
    }
}
