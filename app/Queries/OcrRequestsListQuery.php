<?php

namespace App\Queries;

use App\Models\OCRRequest;
use App\Models\OCRRequestStatus;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Queries\Sorts\OcrRequestStatusSort;
use App\Queries\Filters\CreatedBetweenFilter;
use App\Queries\Filters\OcrRequestStatusFilter;

class OcrRequestsListQuery extends QueryBuilder
{
    public function __construct()
    {
        $firstOrderIdJsonExtract = "json_extract(s.status_metadata, '$.order_id_list[0]')";
        $query = OCRRequest::query()
                ->select([
                    't_job_latest_state.*',
                    DB::raw("{$firstOrderIdJsonExtract} as first_order_id"),
                    'u.name as upload_user_name',
                    DB::raw("json_extract(s_is.status_metadata, '$.source_summary.source_email_from_address') as email_from_address"),
                    'o.bill_to_address_id as first_order_bill_to_address_id',
                ])
                ->join('t_job_state_changes as s', 't_job_latest_state.t_job_state_changes_id', '=', 's.id')
                ->leftJoin('t_orders as o', DB::raw($firstOrderIdJsonExtract), '=', 'o.id')
                ->leftJoin('t_addresses as a', 'o.bill_to_address_id', '=', 'a.id')
                ->leftJoin('t_job_state_changes as s_is', function ($join) {
                    $join->on('t_job_latest_state.request_id', '=', 's_is.request_id')
                    ->where('s_is.status', OCRRequestStatus::INTAKE_STARTED);
                })
                ->leftJoin('t_job_state_changes as s_ur', function ($join) {
                    $join->on('t_job_latest_state.request_id', '=', 's_ur.request_id')
                    ->where('s_ur.status', OCRRequestStatus::UPLOAD_REQUESTED);
                })
                ->leftJoin('users as u', DB::raw("json_extract(s_ur.status_metadata, '$.user_id')"), '=', 'u.id')
                ->when(! is_superadmin() && currentCompany(), function ($query) {
                    return $query->where('s.company_id', '=', currentCompany()->id);
                })
                ->whereNull('t_job_latest_state.order_id')
                ->withCount('orders')
                ->with([
                    'latestOcrRequestStatus:id,status,status_date,status_metadata',
                    'firstOrderBillToAddress',
                ]);

        parent::__construct($query);

        $this->allowedFilters([
            AllowedFilter::partial('request_id', 't_job_latest_state.request_id'),
            AllowedFilter::custom('created_between', new CreatedBetweenFilter(), 't_job_latest_state.created_at'),
            AllowedFilter::custom('status', new OcrRequestStatusFilter()),
            AllowedFilter::custom('display_status', new OcrRequestStatusFilter()),
            AllowedFilter::callback('query', function ($query, $value) {
                $query->where(function ($query) use ($value) {
                    $query->orWhere('a.location_name', 'like', "%{$value}%")
                    ->orWhere('t_job_latest_state.request_id', 'like', "%{$value}%");
                });
            }),
        ])
        ->defaultSort('-t_job_latest_state.created_at')
        ->allowedSorts([
            AllowedSort::field('id', 't_job_latest_state.id'),
            AllowedSort::field('request_id', 't_job_latest_state.request_id'),
            AllowedSort::field('created_at', 't_job_latest_state.created_at'),
            AllowedSort::custom('status', new OcrRequestStatusSort()),
        ]);
    }
}