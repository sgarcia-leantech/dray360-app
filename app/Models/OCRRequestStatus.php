<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property \Illuminate\Database\Eloquent\Collection $ocrRequests
 * @property \App\Models\OCRRequest $ocrRequest
 * @property string $request_id
 * @property string|\Carbon\Carbon $status_date
 * @property string $status
 * @property array $status_metadata
 */
class OCRRequestStatus extends Model
{
    const INTAKE_ACCEPTED = 'intake-accepted',
    INTAKE_EXCEPTION = 'intake-exception',
    INTAKE_REJECTED = 'intake-rejected',
    INTAKE_STARTED = 'intake-started',
    OCR_COMPLETED = 'ocr-completed',
    OCR_POST_PROCESSING_COMPLETE = 'ocr-post-processing-complete',
    OCR_POST_PROCESSING_ERROR = 'ocr-post-processing-error',
    OCR_WAITING = 'ocr-waiting',
    PROCESS_OCR_OUTPUT_FILE_COMPLETE = 'process-ocr-output-file-complete',
    PROCESS_OCR_OUTPUT_FILE_ERROR = 'process-ocr-output-file-error',
    UPLOAD_REQUESTED = 'upload-requested',

    SENDING_TO_WINT = 'sending-to-wint',
    FAILURE_SENDING_TO_WINT = 'failure-sending-to-wint',
    SUCCESS_SENDING_TO_WINT = 'success-sending-to-wint',
    SHIPMENT_CREATED_BY_WINT = 'shipment-created-by-wint',
    SHIPMENT_NOT_CREATED_BY_WINT = 'shipment-not-created-by-wint',

    UPDATING_TO_WINT = 'updating-to-wint',
    FAILURE_UPDATING_TO_WINT = 'failure-updating-to-wint',
    SUCCESS_UPDATING_TO_WINT = 'success-updating-to-wint',
    SHIPMENT_UPDATED_BY_WINT = 'shipment-updated-by-wint',
    SHIPMENT_NOT_UPDATED_BY_WINT = 'shipment-not-updated-by-wint'
    ;

    const STATUS_MAP = [
        self::INTAKE_ACCEPTED => 'Processing',
        self::INTAKE_EXCEPTION => 'Exception',
        self::INTAKE_REJECTED => 'Rejected',
        self::INTAKE_STARTED => 'Intake',
        self::OCR_COMPLETED => 'Processing',
        self::OCR_POST_PROCESSING_COMPLETE => 'Verified',
        self::OCR_POST_PROCESSING_ERROR => 'Rejected',
        self::OCR_WAITING => 'Processing',
        self::PROCESS_OCR_OUTPUT_FILE_COMPLETE => 'Verified',
        self::PROCESS_OCR_OUTPUT_FILE_ERROR => 'Rejected',
        self::UPLOAD_REQUESTED => 'Intake',

        self::SENDING_TO_WINT => 'Sending to TMS',
        self::FAILURE_SENDING_TO_WINT => 'Rejected',
        self::SUCCESS_SENDING_TO_WINT => 'Sent to TMS',
        self::SHIPMENT_CREATED_BY_WINT => 'Accepted by TMS',
        self::SHIPMENT_NOT_CREATED_BY_WINT => 'Rejected',

        self::UPDATING_TO_WINT => 'Sending to TMS',
        self::FAILURE_UPDATING_TO_WINT => 'Rejected',
        self::SUCCESS_UPDATING_TO_WINT => 'Sent to TMS',
        self::SHIPMENT_UPDATED_BY_WINT => 'Accepted by TMS',
        self::SHIPMENT_NOT_UPDATED_BY_WINT => 'Rejected'
    ];

    public $table = 't_job_state_changes';

    public $fillable = [
        'request_id',
        'status_date',
        'status',
        'status_metadata',
        'company_id'
    ];

    protected $casts = [
        'status_metadata' => 'json',
        'status_date' => 'date',
    ];

    protected $appends = ['display_status'];

    public function ocrRequests()
    {
        return $this->belongsTo(OCRRequest::class, 'request_id', 'request_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function ocrRequest()
    {
        return $this->hasOne(OCRRequest::class, 't_job_state_changes_id');
    }

    public function getDisplayStatusAttribute()
    {
        return self::STATUS_MAP[$this->status] ?? '-';
    }

    public static function getStatusFromDisplayStatus($displayStatus): array
    {
        return collect(self::STATUS_MAP)
            ->reject(fn ($item, $key) => $item !== $displayStatus)
            ->keys()
            ->toArray();
    }

    public static function createUploadRequest(array $statusMetadata): self
    {
        $data = [
            'request_id' => $statusMetadata['request_id'],
            'status_date' => now(),
            'status' => static::UPLOAD_REQUESTED,
            'status_metadata' => $statusMetadata,
            'company_id' => $statusMetadata['company_id']
        ];

        return static::create($data);
    }
}
