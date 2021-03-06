<?php

namespace App\Queries;

use App\Models\OCRRequest;
use App\Models\OCRRequestStatus;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Queries\Sorts\OcrRequestStatusSort;
use App\Queries\Filters\CreatedBetweenFilter;
use App\Queries\Filters\OcrRequestStatusFilter;

class OcrRequestsListQuery extends QueryBuilder
{
    public function __construct($requestId = null)
    {
        $query = OCRRequest::query()
                ->select([
                    't_job_latest_state.*',
                    'c.name as company_name',
                    'c.id as company_id',
                    DB::raw('(select min(o2.id) from t_orders as o2 where o2.request_id = s.request_id and o2.deleted_at is null) as first_order_id')
                ])
                ->addSelect(['email_from_address' => DB::table('t_job_state_changes', 's_is')
                    ->selectRaw("json_extract(s_is.status_metadata, '$.source_summary.source_email_from_address') as email_from_address")
                    ->whereColumn('t_job_latest_state.request_id', 's_is.request_id')
                    ->where('s_is.status', OCRRequestStatus::INTAKE_STARTED)
                    ->limit(1)
                ])
                ->addSelect(['has_email' => DB::table('t_job_state_changes', 's_is')
                    ->selectRaw("coalesce(json_unquote(json_extract(status_metadata, '$.key_name')),json_unquote(json_extract(status_metadata, '$.event_info.object_key'))) like 'intakeemail%' as has_email")
                    ->whereColumn('t_job_latest_state.request_id', 's_is.request_id')
                    ->whereIn('s_is.status', [OCRRequestStatus::INTAKE_STARTED, OCRRequestStatus::INTAKE_REJECTED])
                    ->limit(1)
                ])
                ->addSelect(['is_ocr_file' => DB::table('t_job_state_changes', 's_is')
                    ->selectRaw("count(request_id) > 0 as is_ocr_file")
                    ->whereColumn('t_job_latest_state.request_id', 's_is.request_id')
                    ->where('s_is.status', OCRRequestStatus::INTAKE_ACCEPTED)
                    ->limit(1)
                ])
                ->addSelect(['has_upload_requested' => DB::table('t_job_state_changes', 's_is')
                    ->selectRaw("count(request_id) > 0 as is_ocr_file")
                    ->whereColumn('t_job_latest_state.request_id', 's_is.request_id')
                    ->where('s_is.status', OCRRequestStatus::UPLOAD_REQUESTED)
                    ->limit(1)
                ])
                ->addSelect(['upload_user_name' => DB::table('t_job_state_changes', 's_ur')
                    ->select('u.name')
                    ->whereColumn('t_job_latest_state.request_id', 's_ur.request_id')
                    ->where('s_ur.status', OCRRequestStatus::UPLOAD_REQUESTED)
                    ->join('users as u', DB::raw("json_extract(s_ur.status_metadata, '$.user_id')"), '=', 'u.id')
                    ->limit(1)
                ])
                ->addSelect(['first_order_bill_to_address_location_name' => DB::table('t_addresses', 'a')
                    ->select('a.location_name')
                    ->join('t_orders as o', function ($join) {
                        $join->on('o.bill_to_address_id', '=', 'a.id');
                    })
                    ->whereColumn('o.request_id', 't_job_latest_state.request_id')
                    ->orderBy('o.id')
                    ->limit(1)
                ])
                ->addSelect(['tms_template_name' => DB::table('t_orders', 'o')
                    ->select('di.item_display_name')
                    ->join('t_dictionary_items as di', 'di.id', '=', 'o.tms_template_dictid')
                    ->whereColumn('o.request_id', 't_job_latest_state.request_id')
                    ->whereNotNull('tms_template_dictid')
                    ->orderBy('o.id')
                    ->limit(1)
                ])
                ->join('t_job_state_changes as s', 't_job_latest_state.t_job_state_changes_id', '=', 's.id')
                ->join('t_companies as c', 's.company_id', '=', 'c.id')
                ->when(! auth()->user()->isAbleTo('all-companies-view') && currentCompany(), function ($query) {
                    return $query->where('s.company_id', '=', currentCompany()->id);
                })
                ->whereNull('t_job_latest_state.order_id')
                ->whereNotIn('s.status', OCRRequestStatus::HIDE_FROM_REQUESTS_LIST) // list of statuses to exclude from request list
                ->withCount([
                    'orders',
                    'orders as deleted_orders_count' => fn (Builder $q) => $q->onlyTrashed()
                ])
                ->with([
                    'latestOcrRequestStatus:id,status,status_date,status_metadata',
                ])
                ->with(['locks.user' => function ($with) {
                    $with->select('id', 'name', 't_company_id');
                }])
                ->when($requestId, function ($query) use ($requestId) {
                    return $query->orderByDesc(DB::raw("\"{$requestId}\" = t_job_latest_state.request_id"));
                });

        parent::__construct($query);

        $this->allowedFilters([
            AllowedFilter::partial('request_id', 't_job_latest_state.request_id'),
            AllowedFilter::exact('company_id', 's.company_id', false),
            AllowedFilter::custom('created_between', new CreatedBetweenFilter(), 't_job_latest_state.created_at'),
            AllowedFilter::custom('status', new OcrRequestStatusFilter()),
            AllowedFilter::custom('display_status', new OcrRequestStatusFilter()),
            AllowedFilter::callback('query', function ($query, $value) {
                $query
                    ->where(function ($query) use ($value) {
                        $query->orWhereHas('orders', function ($query) use ($value) {
                            $query->where('unit_number', 'like', "%{$value}%")
                                ->orWhere('id', $value);
                        })
                        ->orWhereHas('orders.billToAddress', function ($query) use ($value) {
                            $query->where('location_name', 'like', "%{$value}%");
                        })
                        ->orWhere('t_job_latest_state.request_id', 'like', "%{$value}%");
                    });
            }),
            AllowedFilter::callback('show_done', function ($query, $value) {
                if (! $value) {
                    $query->whereNull('done_at');
                }
            })->default(false)
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
