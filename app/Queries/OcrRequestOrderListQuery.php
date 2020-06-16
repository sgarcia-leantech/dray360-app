<?php

namespace App\Queries;

use App\Models\OCRRequest;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Queries\Sorts\OcrRequestStatusSort;

class OcrRequestOrderListQuery extends QueryBuilder
{
    public function __construct()
    {
        $query = OCRRequest::query()
            ->leftJoin('t_orders', 't_orders.request_id', '=', 't_job_latest_state.request_id')
            ->select('t_job_latest_state.*')
            ->addSelect('t_orders.id as t_order_id')
            ->with([
                'order:id,request_id,bill_to_address_raw_text,created_at,equipment_type,shipment_designation,shipment_direction',
                'latestOcrRequestStatus:id,status,status_date',
            ]);

        parent::__construct($query);

        $this->allowedFilters([
            AllowedFilter::partial('request_id', 't_job_latest_state.request_id'),
            AllowedFilter::partial('order.bill_to_address_raw_text', 't_orders.bill_to_address_raw_text', false),
            AllowedFilter::partial('order.port_ramp_of_origin_address_raw_text', 't_orders.port_ramp_of_origin_address_raw_text', false),
            AllowedFilter::partial('order.port_ramp_of_destination_address_raw_text', 't_orders.port_ramp_of_destination_address_raw_text', false),
            AllowedFilter::partial('order.equipment_type', 't_orders.equipment_type', false),
            AllowedFilter::partial('order.shipment_designation', 't_orders.shipment_designation', false),
            AllowedFilter::partial('order.shipment_direction', 't_orders.shipment_direction', false),
            AllowedFilter::exact('status', 'latestOcrRequestStatus.status'),
            AllowedFilter::callback('query', function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->orWhere('t_orders.bill_to_address_raw_text', 'like', "%{$value}%")
                    ->orWhere('t_job_latest_state.request_id', 'like', "%{$value}%")
                    ->orWhere('t_orders.port_ramp_of_origin_address_raw_text', 'like', "%{$value}%")
                    ->orWhere('t_orders.port_ramp_of_destination_address_raw_text', 'like', "%{$value}%")
                    ->orWhere('t_orders.equipment_type', 'like', "%{$value}%")
                    ->orWhere('t_orders.shipment_designation', 'like', "%{$value}%")
                    ->orWhere('t_orders.shipment_direction', 'like', "%{$value}%");
                });
            }),
        ])
        ->defaultSort('-t_job_latest_state.created_at')
        ->allowedSorts([
            AllowedSort::field('request_id', 't_job_latest_state.request_id'),
            AllowedSort::field('order.bill_to_address_raw_text', 't_orders.bill_to_address_raw_text'),
            AllowedSort::field('order.created_at', 't_orders.created_at'),
            AllowedSort::field('order.equipment_type', 't_orders.equipment_type'),
            AllowedSort::field('order.shipment_designation', 't_orders.shipment_designation'),
            AllowedSort::field('order.shipment_direction', 't_orders.shipment_direction'),
            AllowedSort::custom('status', new OcrRequestStatusSort()),
        ]);
    }
}
