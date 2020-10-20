<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \Illuminate\Database\Eloquent\Collection $orders
 * @property \Illuminate\Database\Eloquent\Collection $statusList
 * @property \App\Models\OCRRequestStatus $latestOcrRequestStatus
 * @property string $request_id
 * @property string $t_job_state_changes_id
 * @property \Carbon\Carbon $created_at intake_date
 * @property \Carbon\Carbon $updated_at latest_status_date
 */
class OCRRequest extends Model
{
    use SoftDeletes;

    public $table = 't_job_latest_state';

    protected $casts = [];

    public $fillable = [
        'request_id',
        't_job_state_changes_id',
        'order_id'
    ];

    public function orders()
    {
        return $this->hasMany(\App\Models\Order::class, 'request_id', 'request_id');
    }

    public function statusList()
    {
        return $this->hasMany(OCRRequestStatus::class, 'request_id', 'request_id');
    }

    public function latestOcrRequestStatus()
    {
        return $this->belongsTo(OCRRequestStatus::class, 't_job_state_changes_id');
    }

    /**
     * Dynamic relationship used in the OcrRequestOrderListQuery, to take advantage of the eager loading.
     * AKA dynamic relationship.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 't_order_id')
            ->withDefault(function (Order $order) {
                $order->fillWithNulls([
                    'request_id',
                    'bill_to_address_raw_text',
                    'created_at',
                    'equipment_type',
                    'shipment_designation',
                    'shipment_direction',
                    'tms_shipment_id',
                ]);
            });
    }

    /**
     * This is only used in the OcrRequestsListQuery class, to take advantage of the eager loading.
     * AKA dynamic relationship.
     */
    public function firstOrderBillToAddress()
    {
        return $this->belongsTo(Address::class, 'first_order_bill_to_address_id');
    }
}
