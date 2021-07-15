<?php

namespace App\Models;

use App\Events\AddressVerified;
use App\Models\Traits\HasLocks;
use App\Events\AttributeVerified;
use App\Models\Traits\MapsAudits;
use Illuminate\Support\Facades\DB;
use App\Models\Traits\FillWithNulls;
use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Traits\ValidatesAddresses;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\VerifiesUserSelectedAttributes;

/**
 * @property \Illuminate\Database\Eloquent\Collection $orderAddressEvents
 * @property \Illuminate\Database\Eloquent\Collection $orderLineItems
 * @property \App\Models\OCRRequest $ocrRequest
 * @property \App\Models\Address $billToAddress
 * @property \App\Models\Address $portRampOfOriginAddress
 * @property \App\Models\Address $portRampOfDestinationAddress
 * @property string $id
 * @property string $request_id
 * @property string $shipment_designation
 * @property string $equipment_type_raw_text
 * @property string $shipment_direction
 * @property boolean $one_way
 * @property string $unit_number
 * @property string $equipment_size
 * @property boolean $hazardous
 * @property boolean $expedite
 * @property string $reference_number
 * @property string $seal_number
 * @property string $vessel
 * @property string $voyage
 * @property string $master_bol_mawb
 * @property string $house_bol_hawb
 * @property string $booking_number
 * @property string $bill_of_lading
 * @property string $bill_to_address_id
 * @property string $port_ramp_of_origin_address_id
 * @property string $port_ramp_of_destination_address_id
 * @property array $ocr_data
 * @property string $pickup_number
 * @property \Carbon\Carbon $pickup_by_date
 * @property \Carbon\Carbon $pickup_by_time
 * @property \Carbon\Carbon $cutoff_date
 * @property \Carbon\Carbon $cutofF_time
 * @property boolean $bill_to_address_verified
 * @property string $bill_to_address_raw_text
 * @property boolean $port_ramp_of_origin_address_verified
 * @property string $port_ramp_of_origin_address_raw_text
 * @property boolean $port_ramp_of_destination_address_verified
 * @property string $port_ramp_of_destination_address_raw_text
 * @property string $variant_id
 * @property string $variant_name
 * @property string $t_tms_provider_id
 * @property string $tms_shipment_id
 * @property string $carrier
 * @property string $preceded_by_order_id
 * @property string $succeded_by_order_id
 * @property \Carbon\Carbon $tms_submission_datetime
 * @property \Carbon\Carbon $tms_cancelled_datetime
 * @property integer $interchange_count
 * @property integer $interchange_err_count
 * @property string $tms_template_id
 */
class Order extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use MapsAudits;
    use SoftDeletes;
    use FillWithNulls;
    use BelongsToCompany;
    use ValidatesAddresses;
    use VerifiesUserSelectedAttributes;
    use HasLocks;

    public $table = 't_orders';

    const CREATED_AT = 'created_at',
        UPDATED_AT = 'updated_at';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'request_id',
        'shipment_designation',
        'equipment_type_raw_text',
        'shipment_direction',
        'one_way',
        'unit_number',
        'equipment_size',
        'hazardous',
        'reference_number',
        'seal_number',
        'vessel',
        'voyage',
        'master_bol_mawb',
        'house_bol_hawb',
        'booking_number',
        'bill_of_lading',
        'bill_to_address_id',
        'port_ramp_of_origin_address_id',
        'port_ramp_of_destination_address_id',
        'ocr_data',
        'pickup_number',
        'pickup_by_date',
        'pickup_by_time',
        'cutoff_date',
        'cutoff_time',
        'bill_to_address_verified',
        'bill_to_address_raw_text',
        'port_ramp_of_origin_address_verified',
        'port_ramp_of_origin_address_raw_text',
        'port_ramp_of_destination_address_verified',
        'port_ramp_of_destination_address_raw_text',
        'variant_id',
        'variant_name',
        'tms_shipment_id',
        'carrier',
        'bill_charge',
        'bill_comment',
        'line_haul',
        'rate_box',
        'fuel_surcharge',
        'total_accessorial_charges',
        'equipment_provider',
        'actual_destination',
        'actual_origin',
        'customer_number',
        'expedite',
        'load_number',
        'purchase_order_number',
        'release_number',
        'ship_comment',
        't_company_id',
        't_tms_provider_id',
        'division_code',
        't_equipment_type_id',
        'equipment_type_verified',
        'preceded_by_order_id',
        'succeded_by_order_id',
        'tms_submission_datetime',
        'tms_cancelled_datetime',
        'interchange_count',
        'interchange_err_count',
        'tms_template_id',
        'tms_template_dictid',
        'container_dictid',
        'container_dictid_verified',
        'tms_template_dictid_verified',
        'is_hidden',
        'carrier_dictid',
        'carrier_dictid_verified',
        'vessel_dictid',
        'vessel_dictid_verified',
        'cc_loadtype',
        'cc_loadtype_dictid',
        'cc_loadtype_dictid_verified',
        'cc_orderstatus',
        'cc_orderstatus_dictid',
        'cc_orderstatus_dictid_verified',
        'cc_haulclass',
        'cc_haulclass_dictid',
        'cc_haulclass_dictid_verified',
        'cc_orderclass',
        'cc_orderclass_dictid',
        'cc_orderclass_dictid_verified',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'one_way' => 'boolean',
        'hazardous' => 'boolean',
        'expedite' => 'boolean',
        'bill_to_address_verified' => 'boolean',
        'port_ramp_of_origin_address_verified' => 'boolean',
        'port_ramp_of_destination_address_verified' => 'boolean',
        'ocr_data' => 'json',
        'pickup_by_date' => 'datetime:Y-m-d',
        'cutoff_date' => 'datetime:Y-m-d',
        'equipment_type_verified' => 'boolean',
        'tms_submission_datetime' => 'datetime',
        'tms_cancelled_datetime' => 'datetime',
        'submitted_date' => 'datetime',
        'tms_template_dictid_verified' => 'boolean',
        'is_hidden' => 'boolean',
        'carrier_dictid_verified' => 'boolean',
        'vessel_dictid_verified' => 'boolean',
        'container_dictid_verified' => 'boolean',
        'cc_loadtype_dictid_verified' => 'boolean',
        'cc_orderstatus_dictid_verified' => 'boolean',
        'cc_haulclass_dictid_verified' => 'boolean',
        'cc_orderclass_dictid_verified' => 'boolean',
    ];

    /**
     * Validation rules
     * Important: Don't add since we don't want to allow those fields to be edited:
     * - ocr_data
     * - request_id
     * - bill_to_address_raw_text
     * - port_ramp_of_origin_address_raw_text
     * - port_ramp_of_destination_address_raw_text
     *
     * @var array
     */
    public static $rules = [
        'shipment_designation' => 'sometimes|nullable',
        'shipment_direction' => 'sometimes|nullable',
        'one_way' => 'sometimes|nullable',
        'unit_number' => 'sometimes|nullable',
        'equipment_size' => 'sometimes|nullable',
        'hazardous' => 'sometimes|nullable',
        'reference_number' => 'sometimes|nullable',
        'seal_number' => 'sometimes|nullable',
        'vessel' => 'sometimes|nullable',
        'voyage' => 'sometimes|nullable',
        'master_bol_mawb' => 'sometimes|nullable',
        'house_bol_hawb' => 'sometimes|nullable',
        'booking_number' => 'sometimes|nullable',
        'bill_of_lading' => 'sometimes|nullable',
        'bill_to_address_id' => 'sometimes|nullable|exists:t_addresses,id',
        'port_ramp_of_origin_address_id' => 'sometimes|nullable|exists:t_addresses,id',
        'port_ramp_of_destination_address_id' => 'sometimes|nullable|exists:t_addresses,id',
        'pickup_number' => 'sometimes|nullable',
        'pickup_by_date' => 'sometimes|nullable',
        'pickup_by_time' => 'sometimes|nullable',
        'cutoff_date' => 'sometimes|nullable',
        'cutoff_time' => 'sometimes|nullable',
        'bill_to_address_verified' => 'sometimes|nullable',
        'port_ramp_of_origin_address_verified' => 'sometimes|nullable',
        'port_ramp_of_destination_address_verified' => 'sometimes|nullable',
        'variant_id' => 'sometimes|nullable',
        'variant_name' => 'sometimes|nullable',
        't_tms_provider_id' => 'sometimes|nullable|exists:t_tms_providers,id',
        'tms_shipment_id' => 'sometimes|nullable',
        'carrier' => 'sometimes|nullable',
        'bill_charge' => 'sometimes|nullable',
        'bill_comment' => 'sometimes|nullable',
        'line_haul' => 'sometimes|nullable',
        'rate_box' => 'sometimes|nullable',
        'fuel_surcharge' => 'sometimes|nullable',
        'total_accessorial_charges' => 'sometimes|nullable',
        'equipment_provider' => 'sometimes|nullable',
        'actual_destination' => 'sometimes|nullable',
        'actual_origin' => 'sometimes|nullable',
        'customer_number' => 'sometimes|nullable',
        'expedite' => 'sometimes|nullable',
        'load_number' => 'sometimes|nullable',
        'purchase_order_number' => 'sometimes|nullable',
        'release_number' => 'sometimes|nullable',
        'ship_comment' => 'sometimes|nullable',
        'division_code' => 'sometimes|nullable',
        't_equipment_type_id' => 'sometimes|nullable|exists:t_equipment_types,id',
        'equipment_type_verified' => 'sometimes|nullable',
        'tms_submission_datetime' => 'sometimes|nullable',
        'tms_cancelled_datetime' => 'sometimes|nullable',
        'interchange_count' => 'sometimes|nullable',
        'interchange_err_count' => 'sometimes|nullable',
        'tms_template_id' => 'sometimes|nullable',
        'tms_template_dictid' => 'sometimes|nullable',
        'container_dictid' => 'sometimes|nullable',
        'container_dictid_verified' => 'sometimes|nullable',
        'tms_template_dictid_verified' => 'sometimes|nullable',
        'is_hidden' => 'sometimes|nullable',
        'carrier_dictid' => 'sometimes|nullable',
        'carrier_dictid_verified' => 'sometimes|nullable',
        'vessel_dictid' => 'sometimes|nullable',
        'vessel_dictid_verified' => 'sometimes|nullable',
        'cc_loadtype_dictid' => 'sometimes|nullable',
        'cc_loadtype_dictid_verified' => 'sometimes|nullable',
        'cc_orderstatus_dictid' => 'sometimes|nullable',
        'cc_orderstatus_dictid_verified' => 'sometimes|nullable',
        'cc_haulclass_dictid' => 'sometimes|nullable',
        'cc_haulclass_dictid_verified' => 'sometimes|nullable',
        'cc_orderclass_dictid' => 'sometimes|nullable',
        'cc_orderclass_dictid_verified' => 'sometimes|nullable',
    ];

    /**
     * Attributes that will be checked when they change from `false` to `true`.
     */
    public static $verifiableAttributes = [
        'bill_to_address_verified' => AddressVerified::class,
        'tms_template_dictid_verified' => AttributeVerified::class,
        'carrier_dictid_verified' => AttributeVerified::class,
        'vessel_dictid_verified' => AttributeVerified::class,
        'container_dictid_verified' => AttributeVerified::class,
        'cc_loadtype_dictid_verified' => AttributeVerified::class,
        'cc_orderstatus_dictid_verified' => AttributeVerified::class,
        'cc_haulclass_dictid_verified' => AttributeVerified::class,
        'cc_orderclass_dictid_verified' => AttributeVerified::class,
    ];

    protected $objectLockType = ObjectLock::REQUEST_OBJECT;
    protected $objectLockLocalKey = 'request_id';

    public function precededByOrder()
    {
        return $this->belongsTo(Order::class, 'preceded_by_order_id');
    }

    public function succededByOrder()
    {
        return $this->belongsTo(Order::class, 'succeded_by_order_id');
    }

    public function orderAddressEvents()
    {
        return $this->hasMany(OrderAddressEvent::class, 't_order_id')
            ->orderBy('event_number');
    }

    public function orderLineItems()
    {
        return $this->hasMany(OrderLineItem::class, 't_order_id');
    }

    public function ocrRequest()
    {
        return $this->hasOne(OCRRequest::class, 'order_id');
    }

    public function billToAddress()
    {
        return $this->belongsTo(Address::class, 'bill_to_address_id');
    }

    public function portRampOfOriginAddress()
    {
        return $this->belongsTo(Address::class, 'port_ramp_of_origin_address_id');
    }

    public function portRampOfDestinationAddress()
    {
        return $this->belongsTo(Address::class, 'port_ramp_of_destination_address_id');
    }

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class, 't_equipment_type_id');
    }

    public function tmsProvider()
    {
        return $this->belongsTo(TMSProvider::class, 't_tms_provider_id');
    }

    public function tmsTemplate()
    {
        return $this->belongsTo(DictionaryItem::class, 'tms_template_dictid');
    }

    public function container()
    {
        return $this->belongsTo(DictionaryItem::class, 'container_dictid');
    }

    public function ccLoadtype()
    {
        return $this->belongsTo(DictionaryItem::class, 'cc_loadtype_dictid');
    }

    public function ccOrderStatus()
    {
        return $this->belongsTo(DictionaryItem::class, 'cc_orderstatus_dictid');
    }

    public function ccHaulClass()
    {
        return $this->belongsTo(DictionaryItem::class, 'cc_haulclass_dictid');
    }

    public function ccOrderClass()
    {
        return $this->belongsTo(DictionaryItem::class, 'cc_orderclass_dictid');
    }

    public function siblings()
    {
        return $this->hasMany(self::class, 'request_id', 'request_id');
    }

    public function comments()
    {
        return $this->morphMany(FeedbackComment::class, 'commentable');
    }

    public static function getBasicOrderForSideBySide($orderId): self
    {
        return Order::query()
            ->select('t_orders.*')
            ->addSelect(['email_from_address' => DB::table('t_job_state_changes', 's_is')
                ->selectRaw("json_extract(s_is.status_metadata, '$.source_summary.source_email_from_address') as email_from_address")
                ->whereColumn('t_orders.request_id', 's_is.request_id')
                ->where('s_is.status', OCRRequestStatus::INTAKE_STARTED)
                ->limit(1)
            ])
            ->addSelect(['upload_user_name' => DB::table('t_job_state_changes', 's_ur')
                ->select('u.name')
                ->whereColumn('t_orders.request_id', 's_ur.request_id')
                ->where('s_ur.status', OCRRequestStatus::UPLOAD_REQUESTED)
                ->join('users as u', DB::raw("json_extract(s_ur.status_metadata, '$.user_id')"), '=', 'u.id')
                ->limit(1)
            ])
            ->addSelect(['submitted_date' => DB::table('t_job_state_changes', 'sub_date_state')
                ->select('sub_date_state.created_at')
                ->whereColumn('t_orders.request_id', 'sub_date_state.request_id')
                ->orderBy('created_at')
                ->limit(1)
            ])
            ->withCount('siblings')
            ->findOrFail($orderId);
    }

    public function updateRelatedModels($relatedModels): void
    {
        $existingRelatedModels = [
            'orderLineItems' => $relatedModels['order_line_items'] ?? false
                ? $this->orderLineItems()->get()
                : [],
            'orderAddressEvents' => $relatedModels['order_address_events'] ?? false
                ? $this->orderAddressEvents()->get()
                : [],
        ];

        collect([
            'orderLineItems' => $relatedModels['order_line_items'] ?? [],
            'orderAddressEvents' => $relatedModels['order_address_events'] ?? [],
        ])->flatMap(function ($relatedModels, $relationName) {
            return collect($relatedModels)
                ->map(function ($relatedModel, $key) use ($relationName) {
                    if ($relationName == 'orderAddressEvents') {
                        $relatedModel['event_number'] = $key + 1;
                        unset($relatedModel['t_address_raw_text']);
                    }

                    return ['relationship' => $relationName, 'model' => $relatedModel];
                });
        })->each(function ($data) use ($existingRelatedModels) {
            $relationship = $data['relationship'];
            $modelData = $data['model'];

            if (! $modelData) {
                return;
            }

            if ($modelData['id'] == null) {
                $this->{$relationship}()->create($modelData);
                return;
            }

            $relatedModel = $existingRelatedModels[$relationship]->firstWhere('id', $modelData['id']);

            if (! $relatedModel) {
                return;
            }

            if ($modelData['deleted_at'] ?? false) {
                $relatedModel->delete();
                return;
            }

            $relatedModel->update($modelData);
        });

        $this->touch();
    }

    public function loadRelationshipsForSideBySide(): self
    {
        return $this->load([
            'ocrRequest',
            'ocrRequest.latestOcrRequestStatus',
            'orderLineItems',
            'billToAddress',
            'orderAddressEvents',
            'orderAddressEvents.address',
            'equipmentType',
            'tmsTemplate:id,item_key,item_display_name',
        ]);
    }

    public function isTheLastUnderReview(): bool
    {
        $notOnReview = self::query()
            ->where('request_id', $this->request_id)
            ->where('id', '!=', $this->id)
            ->whereDoesntHave('ocrRequest.latestOcrRequestStatus', function ($query) {
                $query->where('status', OCRRequestStatus::PROCESS_OCR_OUTPUT_FILE_REVIEW);
            })
            ->count();

        $total = self::query()
            ->where('request_id', $this->request_id)
            ->count();

        return $total - $notOnReview == 1;
    }

    public function getPostProcessingReviewStatusMetadata()
    {
        $status = OCRRequestStatus::where([
            'status' => OCRRequestStatus::OCR_POST_PROCESSING_REVIEW,
            'request_id' => $this->request_id,
        ])->first(['status', 'status_metadata']);

        return $status->status_metadata ?? [];
    }
}
