<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use App\Models\TMSProvider;
use App\Models\EquipmentType;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class EquipmentTypesSelectValuesController extends Controller
{
    public function __invoke(Company $company, TMSProvider $tmsProvider)
    {
        return response()->json([
            'equipment_types' => EquipmentType::query()
                ->forCompanyAndTmsProvider($company->id, $tmsProvider->id)
                ->select(DB::raw('distinct equipment_type'))
                ->get()
                ->pluck('equipment_type'),
            'equipment_owners' => EquipmentType::query()
                ->forCompanyAndTmsProvider($company->id, $tmsProvider->id)
                ->select(DB::raw('distinct equipment_owner'))
                ->get()
                ->pluck('equipment_owner'),
            'equipment_sizes' => EquipmentType::query()
                ->forCompanyAndTmsProvider($company->id, $tmsProvider->id)
                ->select(DB::raw('distinct equipment_size'))
                ->get()
                ->pluck('equipment_size'),
            'scacs' => EquipmentType::query()
                ->forCompanyAndTmsProvider($company->id, $tmsProvider->id)
                ->select(DB::raw('distinct scac'))
                ->get()
                ->pluck('scac'),
        ]);
    }
}
