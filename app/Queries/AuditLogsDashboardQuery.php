<?php

namespace App\Queries;

use App\Models\Order;
use App\Models\OrderLineItem;
use Illuminate\Support\Carbon;
use App\Models\OrderAddressEvent;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AuditLogsDashboardQuery extends QueryBuilder
{
    public function __construct(array $filters)
    {
        $filters['start_date'] = Carbon::createFromDate($filters['start_date'])
            ->startOfDay()
            ->toDateTimeString();
        $filters['end_date'] = Carbon::createFromDate($filters['end_date'])
            ->endOfDay()
            ->toDateTimeString();
        $auditsFilterQuery = function ($query) use ($filters) {
            $query->where('created_at', '>=', $filters['start_date'])
                ->where('created_at', '<=', $filters['end_date'])
                ->when($filters['user_id'] ?? null, fn ($q) => $q->where('user_id', $filters['user_id']));
        };

        $query = Order::query()
            ->select([
                't_orders.id',
                't_orders.request_id',
                't_orders.variant_name',
                't_orders.t_company_id',
                't_orders.created_at',
                't_orders.updated_at',
            ])
            ->addSelect(['changes_count' => DB::raw("
                    (select coalesce(sum(
                        if(json_length(old_values) = 0, json_length(new_values), json_length(old_values))
                    ), 0)
                    from audits
                    where audits.auditable_type = '". str_replace('\\', '\\\\', Order::class) ."'
                    and audits.auditable_id = t_orders.id
                    )
                    +
                    (select coalesce(sum(
                        if(json_length(old_values) = 0, json_length(new_values), json_length(old_values))
                    ), 0)
                    from audits
                    join t_order_line_items on audits.auditable_id = t_order_line_items.id
                        and t_order_line_items.t_order_id = t_orders.id
                    where audits.auditable_type = '". str_replace('\\', '\\\\', OrderLineItem::class) ."'
                    )
                    +
                    (select coalesce(sum(
                        if(json_length(old_values) = 0, json_length(new_values), json_length(old_values))
                    ),0)
                    from audits
                    join t_order_address_events on audits.auditable_id = t_order_address_events.id
                        and t_order_address_events.t_order_id = t_orders.id
                    where audits.auditable_type = '". str_replace('\\', '\\\\', OrderAddressEvent::class) ."'
                    ) as changes_count
            ")
            ])
            ->with([
                'audits.user',
                'company:id,name',
            ])
            ->with('orderAddressEvents', function ($query) {
                $query
                    ->select(['id', 't_order_id'])
                    ->withTrashed()
                    ->with('audits.user');
            })
            ->with('orderLineItems', function ($query) {
                $query
                    ->select(['id', 't_order_id'])
                    ->withTrashed()
                    ->with('audits.user');
            })
            ->where(function ($where) use ($auditsFilterQuery) {
                $where->orWhereHas('audits', $auditsFilterQuery)
                    ->orWhereHas('orderAddressEvents.audits', $auditsFilterQuery)
                    ->orWhereHas('orderLineItems.audits', $auditsFilterQuery);
            })
            ->whereDoesntHave('audits', function ($query) {
                $query->where('event', 'created');
            })
            ->join('t_companies as c', 'c.id', '=', 't_orders.t_company_id');

        parent::__construct($query);

        $this->allowedFilters([
            AllowedFilter::partial('variant_name', 't_orders.variant_name'),
            AllowedFilter::exact('company_id', 't_orders.t_company_id', false),
        ])
        ->defaultSort('-t_orders.id')
        ->allowedSorts([
            AllowedSort::field('id', 't_orders.id'),
            AllowedSort::field('request_id', 't_orders.request_id'),
            AllowedSort::field('company.name', 'c.name'),
            AllowedSort::field('variant_name', 't_orders.variant_name'),
            AllowedSort::field('created_at', 't_orders.created_at'),
            AllowedSort::field('updated_at', 't_orders.updated_at'),
            AllowedSort::field('changes_count'),
        ])
        ;
    }
}