<?php

namespace App\Queries;

use App\Models\Order;
use App\Models\OCRRequestStatus;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Queries\Filters\OrderStatusFilter;
use App\Queries\Filters\CreatedBetweenFilter;

class OrdersListQuery extends QueryBuilder
{
    public function __construct()
    {
        $hasPdfSelect = <<<ENDOFSQL
            if (
                json_extract(s_pdf.status_metadata, '$.document_archive_location') is null,
                0,
                1
            ) as has_pdf
        ENDOFSQL;
        $query = Order::query()
            ->select([
                't_orders.id',
                't_orders.request_id',
                't_orders.created_at',
                't_orders.equipment_type',
                't_orders.shipment_designation',
                't_orders.shipment_direction',
                't_orders.tms_shipment_id',
                't_orders.bill_to_address_id',
                't_orders.unit_number',
                't_orders.reference_number',
            ])
            // ->addSelect(['has_pdf' => OCRRequestStatus::from('t_job_state_changes', 's_pdf')
            //     ->select(DB::raw($hasPdfSelect))
            //     ->whereColumn('s_pdf.request_id', 't_orders.request_id')
            //     ->where('status', OCRRequestStatus::INTAKE_ACCEPTED)
            //     ->orWhere('status', OCRRequestStatus::INTAKE_ACCEPTED_DATAFILE)
            //     ->limit(1)
            // ])
            ->addSelect(DB::raw('1 as has_pdf'))
            ->leftJoin('t_addresses as bill_to', 'bill_to.id', '=', 't_orders.bill_to_address_id')
            ->join('t_job_latest_state as ls_sort', 'ls_sort.order_id', '=', 't_orders.id')
            ->join('t_job_state_changes as s_sort', 's_sort.id', '=', 'ls_sort.t_job_state_changes_id')
            ->when(! is_superadmin() && currentCompany(), function ($query) {
                return $query->where('t_orders.t_company_id', '=', currentCompany()->id);
            })
            ->with([
                'ocrRequest:order_id,created_at,updated_at,t_job_state_changes_id',
                'ocrRequest.latestOcrRequestStatus:id,status,status_metadata',
                'billToAddress:id,location_name',
            ]);

        parent::__construct($query);

        $this->allowedFilters([
            AllowedFilter::partial('request_id', 't_orders.request_id'),
            AllowedFilter::custom('created_between', new CreatedBetweenFilter(), 't_orders.created_at'),
            AllowedFilter::custom('status', new OrderStatusFilter()),
            AllowedFilter::custom('display_status', new OrderStatusFilter()),
            AllowedFilter::callback('query', function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->orWhere('t_orders.request_id', 'like', "{$value}%")
                        ->orWhere('t_orders.equipment_type', 'like', "{$value}%")
                        ->orWhere('t_orders.shipment_designation', 'like', "{$value}%")
                        ->orWhere('t_orders.shipment_direction', 'like', "{$value}%")
                        ->orWhere('t_orders.tms_shipment_id', 'like', "{$value}%")
                        ->orWhere('t_orders.unit_number', 'like', "{$value}%")
                        ->orWhere('t_orders.reference_number', 'like', "{$value}%")
                        ->orWhere('bill_to.location_name', 'like', "{$value}%");
                });
            }),
        ])
        ->defaultSort('-t_orders.created_at', '-t_orders.id')
        ->allowedSorts([
            AllowedSort::field('request_id', 't_orders.request_id'),
            AllowedSort::field('created_at', 't_orders.created_at'),
            AllowedSort::field('status', 's_sort.status'),
            AllowedSort::field('order.equipment_type', 't_orders.equipment_type'),
            AllowedSort::field('order.shipment_designation', 't_orders.shipment_designation'),
            AllowedSort::field('order.shipment_direction', 't_orders.shipment_direction'),
            AllowedSort::field('order.bill_to_address', 'bill_to.location_name'),
        ]);
    }
}
