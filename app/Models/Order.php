<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Order
 * @package App\Models
 * @version March 5, 2020, 8:00 pm UTC
 *
 * @property \Illuminate\Database\Eloquent\Collection orderAddressEvents
 * @property \Illuminate\Database\Eloquent\Collection orderLineItems
 * @property string request_id
 * @property string shipment_designation
 * @property string equipment_type
 * @property string shipment_direction
 * @property boolean one_way
 * @property boolean yard_pre_pull
 * @property boolean has_chassis
 * @property string unit_number
 * @property string equipment
 * @property string equipment_size
 * @property string owner_or_ss_company
 * @property boolean hazardous
 * @property boolean expedite_shipment
 * @property string reference_number
 * @property string rate_quote_number
 * @property string seal_number_list
 * @property string port_ramp_of_origin
 * @property string port_ramp_of_destination
 * @property string vessel
 * @property string voyage
 * @property string master_bol_mawb
 * @property string house_bol_hawb
 * @property string|\Carbon\Carbon estimated_arrival_utc
 * @property string|\Carbon\Carbon last_free_date_utc
 */
class Order extends Model
{
    use SoftDeletes;

    public $table = 't_orders';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';


    protected $dates = ['deleted_at'];



    public $fillable = [
        'request_id',
        'shipment_designation',
        'equipment_type',
        'shipment_direction',
        'one_way',
        'yard_pre_pull',
        'has_chassis',
        'unit_number',
        'equipment',
        'equipment_size',
        'owner_or_ss_company',
        'hazardous',
        'expedite_shipment',
        'reference_number',
        'rate_quote_number',
        'seal_number_list',
        'port_ramp_of_origin',
        'port_ramp_of_destination',
        'vessel',
        'voyage',
        'master_bol_mawb',
        'house_bol_hawb',
        'estimated_arrival_utc',
        'last_free_date_utc'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'request_id' => 'string',
        'shipment_designation' => 'string',
        'equipment_type' => 'string',
        'shipment_direction' => 'string',
        'one_way' => 'boolean',
        'yard_pre_pull' => 'boolean',
        'has_chassis' => 'boolean',
        'unit_number' => 'string',
        'equipment' => 'string',
        'equipment_size' => 'string',
        'owner_or_ss_company' => 'string',
        'hazardous' => 'boolean',
        'expedite_shipment' => 'boolean',
        'reference_number' => 'string',
        'rate_quote_number' => 'string',
        'seal_number_list' => 'string',
        'port_ramp_of_origin' => 'string',
        'port_ramp_of_destination' => 'string',
        'vessel' => 'string',
        'voyage' => 'string',
        'master_bol_mawb' => 'string',
        'house_bol_hawb' => 'string',
        'estimated_arrival_utc' => 'datetime',
        'last_free_date_utc' => 'datetime'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function orderAddressEvents()
    {
        return $this->hasMany(\App\Models\OrderAddressEvent::class, 't_order_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function orderLineItems()
    {
        return $this->hasMany(\App\Models\OrderLineItem::class, 't_order_id');
    }
}
