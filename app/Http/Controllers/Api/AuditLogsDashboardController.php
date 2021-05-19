<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use App\Http\Controllers\Controller;
use App\Queries\AuditLogsDashboardQuery;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogsDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('viewAny', Audit::class);

        $filters = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'user_id' => 'sometimes|integer|exists:users,id',
            'filter[company_id]' => 'sometimes|integer|exists:t_companies,id',
            'filter[variant_name]' => 'sometimes|string',
        ]);

        return JsonResource::collection($this->getAuditLogData($filters));
    }

    protected function getAuditLogData(array $filters)
    {
        $paginator = (new AuditLogsDashboardQuery($filters))->paginate();

        $items = collect($paginator->items())->map(function ($order) {
            return [
                    'model' => $order->toArray(),
                    'order' => $order->getAttributesChanges(),
                    'order_address_events' => $order->orderAddressEvents->map(function ($orderAddressEvent) {
                        return [
                            'id' => $orderAddressEvent->id,
                            'audits' => $orderAddressEvent->getAttributesChanges(),
                        ];
                    }),
                    'order_line_items' => $order->orderLineItems->map(function ($orderLineItem) {
                        return [
                            'id' => $orderLineItem->id,
                            'audits' => $orderLineItem->getAttributesChanges(),
                        ];
                    }),
                ];
        });

        return $paginator->setCollection($items);
    }
}