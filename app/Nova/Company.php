<?php

namespace App\Nova;

use stdClass;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Fields\BelongsTo;

class Company extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\Company::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'User Management';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name', 'email_intake_address', 'email_intake_address_alt',
    ];

    /**
     * The relationships that should be eager loaded on index queries.
     *
     * @var array
     */
    public static $with = ['domain'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make('Id', 'id')->sortable(),
            Text::make('Name')->rules([
                'required',
                'alpha_num',
            ]),
            Text::make('Intake Email', 'email_intake_address')
                ->readonly(function ($request) {
                    return $request->isCreateOrAttachRequest();
                })
                ->help('This email is auto-generated once the company is created'),
            Text::make('Intake Email Alt', 'email_intake_address_alt')
                ->readonly(function ($request) {
                    return $request->isCreateOrAttachRequest();
                }),
            Text::make('Intake Onboarding Email', 'email_onboarding_address')
                ->readonly(function ($request) {
                    return $request->isCreateOrAttachRequest();
                })
                ->help('This email is auto-generated once the company is created'),
            BelongsTo::make('Default TMS Provider', 'defaultTmsProvider', TmsProvider::class)->sortable(),
            BelongsTo::make('Domain', 'domain', Domain::class)->sortable()->nullable(),
            Number::make('Automatic address verification threshold', 'automatic_address_verification_threshold')->hideFromIndex(),
            Boolean::make('Sync Addresses', 'sync_addresses'),
            Boolean::make('Inactive', 'deactivated_at')
                ->resolveUsing(function ($deactivatedAt) {
                    return ! ! $deactivatedAt;
                }),
            Textarea::make('Notes', 'notes')->hideFromIndex(),

            Code::make('Ref Mapping', 'refs_custom_mapping')->json()->hideFromIndex()->rules(['nullable', 'json']),
            Code::make('Configuration', 'configuration')
                ->json()
                ->hideFromIndex()
                ->rules(['nullable', 'json'])
                ->resolveUsing(function ($configuration) {
                    $json = json_decode($configuration, true);

                    if (empty($json)) {
                        return json_encode(new stdClass());
                    }

                    return collect($json)->sortKeys()->toJson(JSON_PRETTY_PRINT);
                }),
            Code::make('Company Configuration', 'company_config')->json()->hideFromIndex()->rules(['nullable', 'json']),

            Text::make('Blackfly token', 'blackfly_token')->hideFromIndex()->nullable(),
            Text::make('Blackfly imagetype', 'blackfly_imagetype')->hideFromIndex()->nullable(),

            Text::make('Ripcms username', 'ripcms_username')->hideFromIndex()->nullable(),
            Text::make('Ripcms password', 'ripcms_password')->hideFromIndex()->nullable(),

            Text::make('Compcare API Key', 'compcare_api_key')->hideFromIndex()->nullable(),

            Text::make('ChainIO URL', 'chainio_url')->hideFromIndex()->nullable(),
            Text::make('ChainIO API Key', 'chainio_api_key')->hideFromIndex()->nullable(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }
}
