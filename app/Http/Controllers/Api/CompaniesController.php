<?php

namespace App\Http\Controllers\Api;

use App\Models\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class CompaniesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:companies-view')->only('index');
    }

    public function index()
    {
        return JsonResource::collection(Company::all());
    }

    public function update(Request $request, Company $company)
    {
        $this->authorize('update', $company );
        $data = $request->validate([
            'refs_comments_mapping' => 'sometimes|json', 
            't_address_id' => 'sometimes|int',   
            'name' => 'sometimes|string',
            'email_intake_address' => 'sometimes|string',
            'email_intake_address_alt' => 'sometimes|string'
        ]);
        
        $company->update($data);

        return response()->json(['data' => $company]);
    }
}
