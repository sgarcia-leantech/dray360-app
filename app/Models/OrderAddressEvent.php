<?php

namespace App\Models;

use App\Events\AddressVerified;
use App\Models\Traits\MapsAudits;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\VerifiesUserSelectedAttributes;

/**
 * @property \App\Models\Address $address
 * @property \App\Models\Order $order
 * @property integer $t_order_id
 * @property integer $t_address_id
 * @property boolean $t_address_verified
 * @property boolean $t_address_raw_text
 * @property integer $event_number
 * @property boolean $is_hook_event
 * @property boolean $is_mount_event
 * @property boolean $is_deliver_event
 * @property boolean $is_dismount_event
 * @property boolean $is_drop_event
 * @property boolean $is_pickup_event
 * @property string $unparsed_event_type
 */
class OrderAddressEvent extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use MapsAudits;
    use SoftDeletes;
    use VerifiesUserSelectedAttributes;

    public $table = 't_order_address_events';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'event_number',
        'is_deliver_event',
        'is_dismount_event',
        'is_drop_event',
        'is_pickup_event',
        'is_hook_event',
        'is_mount_event',
        't_address_id',
        't_address_raw_text',
        't_address_verified',
        't_order_id',
        'unparsed_event_type',
        'note',
    ];

    /**
     * The attributes that should be casted to native types.
     */
    protected $casts = [
        't_address_verified' => 'boolean',
        'event_number' => 'integer',
        'is_hook_event' => 'boolean',
        'is_mount_event' => 'boolean',
        'is_deliver_event' => 'boolean',
        'is_dismount_event' => 'boolean',
        'is_drop_event' => 'boolean',
        'is_pickup_event' => 'boolean',
    ];

    /**
     * Validation rules
     */
    public static $rules = [
        't_order_id' => 'required',
        't_address_id' => 'required',
        'event_number' => 'required'
    ];

    /**
     * Attributes that will be checked when they change from `false` to `true`.
     */
    public static $verifiableAttributes = [
        't_address_verified' => AddressVerified::class,
    ];

    public function address()
    {
        return $this->belongsTo(Address::class, 't_address_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 't_order_id');
    }
}
