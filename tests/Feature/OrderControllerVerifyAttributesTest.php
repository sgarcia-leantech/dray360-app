<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Order;
use App\Models\Company;
use App\Models\TMSProvider;
use Illuminate\Http\Response;
use App\Models\DictionaryItem;
use App\Events\AddressVerified;
use App\Events\AttributeVerified;
use App\Models\OrderAddressEvent;
use Tests\Seeds\OrdersTableSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Testing\Fakes\EventFake;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderControllerVerifyAttributesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAdmin();
        $this->seed(OrdersTableSeeder::class);
    }

    /** @test */
    public function it_should_dispatch_the_event_that_the_tms_template_id_was_verified()
    {
        Event::swap(new EventFake(Event::getFacadeRoot()));
        $order = Order::orderByDesc('id')->first();
        $company = factory(Company::class)->create();
        $template = factory(DictionaryItem::class)->create(['t_company_id' => $company->id]);
        $tmsProvider = factory(TMSProvider::class)->create();
        $orderAddressEvent = factory(OrderAddressEvent::class)->create([
            't_address_verified' => false,
            't_order_id' => $order->id,
        ]);
        $order->update([
            'tms_template_dictid' => $template->id,
            'tms_template_dictid_verified' => false,
            'carrier_dictid_verified' => false,
            'vessel_dictid_verified' => false,
            'bill_to_address_verified' => false,
            'itgcontainer_dictid_verified' => false,
            't_company_id' => $company->id,
            't_tms_provider_id' => $tmsProvider->id,
        ]);

        $this->putJson(route('orders.update', $order->id), [
                'tms_template_dictid_verified' => true,
                'bill_to_address_verified' => true,
                'carrier_dictid_verified' => true,
                'vessel_dictid_verified' => true,
                'itgcontainer_dictid_verified' => true,
                'order_address_events' => [
                    $orderAddressEvent->setAttribute('t_address_verified', true)->toArray()
                ],
            ])
            ->assertStatus(Response::HTTP_OK);

        Event::assertDispatchedTimes(AddressVerified::class, 2);
        Event::assertDispatchedTimes(AttributeVerified::class, 4);
        $count = 0;
        Event::assertDispatched(AttributeVerified::class, function ($event) use (&$count) {
            $wasTriggered = in_array($event->verifiableColumn, [
                'tms_template_dictid_verified',
                'carrier_dictid_verified',
                'vessel_dictid_verified',
                'itgcontainer_dictid_verified',
            ]);

            if ($wasTriggered) {
                $count++;
            }

            return $wasTriggered && $count === 4;
        });
    }
}
