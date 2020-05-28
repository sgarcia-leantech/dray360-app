<?php

namespace App\Http\Controllers;

use App\Models\OCRRule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\OCRRule as ResourcesOCRRule;

class OCRRulesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return new ResourcesOCRRule(OCRRule::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate(OCRRule::$rules);

        $ocrRule = OCRRule::create($data);

        return response()->json($ocrRule, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OCRRule  $oCRRule
     * @return \Illuminate\Http\Response
     */
    public function show(OCRRule $ocrRule)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OCRRule  $oCRRule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OCRRule $ocrRule)
    {
        $data = $request->validate(OCRRule::$rules);

        $ocrRule->update($data);

        return response()->json($ocrRule);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OCRRule  $oCRRule
     * @return \Illuminate\Http\Response
     */
    public function destroy(OCRRule $oCRRule)
    {
    }
}
