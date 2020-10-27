<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\JsonResource;

class SideBySideOrder extends JsonResource
{
    const MINUTES_URI_REMAINS_VALID = 15;
    protected bool $preSignImages;

    public function __construct(Order $order, bool $preSignImages = true)
    {
        parent::__construct(
            $order->loadRelationshipsForSideBySide()->load('precededByOrder')
        );
        self::withoutWrapping();
        $this->preSignImages = $preSignImages;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->preSignImages) {
            $this->preSignDocumentImages();
        }

        $this->preparePreceedingOrderChanges();

        return parent::toArray($request);
    }

    protected function preSignDocumentImages()
    {
        try {
            $ocr_clone = $this->resource->ocr_data;
            // note the & in the foreach specifies pass-by-reference
            foreach ($ocr_clone['page_index_filenames']['value'] as $eachPageIndex => &$eachPage) {
                $s3Config = config('filesystems.disks.s3-base') + [
                    'bucket' => s3_bucket_from_url($eachPage['value']),
                ];
                $storage = Storage::createS3Driver($s3Config);
                $urlExpiryTime = now()->addMinutes(self::MINUTES_URI_REMAINS_VALID);

                // save presigned info on eachPage
                $eachPage['presigned_download_uri'] = $storage->temporaryUrl(
                    s3_file_name_from_url($eachPage['value']),
                    $urlExpiryTime
                );
                $eachPage['presigned_download_uri_expires'] = $urlExpiryTime;
            }
            // assign updated ocr_data clone to order object, replacing old ocr_data
            $this->resource->ocr_data = $ocr_clone;
        } catch (\Exception $e) {
        }
    }

    protected function preparePreceedingOrderChanges()
    {
        if (! $this->resource->precededByOrder) {
            $this->resource
                ->setRelation('precedingOrderChanges', collect())
                ->unsetRelation('precededByOrder');
            return;
        }

        $precededByOrder = $this->resource
            ->precededByOrder
            ->load([
                'orderLineItems',
                'billToAddress',
                'portRampOfDestinationAddress',
                'portRampOfOriginAddress',
                'orderAddressEvents.address',
                'equipmentType',
            ])
            ->toArray();

        $changedValues = collect($precededByOrder)
            ->reject(function ($precedingValue, $key) {
                if ($this->keyShouldBeIgnored($key)) {
                    return true;
                }

                if (in_array($key, ['order_line_items', 'order_address_events'])) {
                    $columnToCompare = $key == 'order_line_items' ? 'contents' : 't_address_id';

                    return $this->relatedItemsAreTheSame(
                        $this->resource->getRelationValue(Str::camel($key))->toArray(),
                        $precedingValue,
                        $columnToCompare
                    );
                }

                return $this->resource->getAttribute($key) == $precedingValue;
            })
            ->mapWithKeys(function ($value, $key) use ($precededByOrder) {
                $baseValue = [$key => $value];

                if (in_array($key, [
                    'port_ramp_of_origin_address_id',
                    'port_ramp_of_destination_address_id',
                    'bill_to_address_id'
                ])) {
                    $relationObjectKey = Str::before($key, '_id');
                    return $baseValue + [$relationObjectKey => $precededByOrder[$relationObjectKey] ?? null];
                }

                return $baseValue;
            });

        $this->resource
            ->setRelation('precedingOrderChanges', $changedValues)
            ->unsetRelation('precededByOrder');
    }

    protected function relatedItemsAreTheSame(array $current, array $preceding, string $columnToCompare): bool
    {
        if (count($current) != count($preceding)) {
            return false;
        }

        return collect($current)
            ->filter(function ($item, $index) use ($preceding, $columnToCompare) {
                return $item[$columnToCompare] == Arr::get($preceding, "{$index}.{$columnToCompare}");
            })->count() == count($current);
    }

    protected function keyShouldBeIgnored(string $key): bool
    {
        return in_array($key, [
            'id',
            'request_id',
            'created_at',
            'updated_at',
            'deleted_at',
            'ocr_data',
            'bill_to_address',
            'port_ramp_of_destination_address',
            'port_ramp_of_origin_address',
            'equipment_type',
            'preceded_by_order_id',
        ]) || Str::contains($key, ['_raw_text', '_verified']);
    }
}