<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Company;
use App\Models\TMSProvider;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Response;
use App\Actions\SendOrderToTms;
use Tests\Seeds\CompaniesSeeder;
use Tests\Seeds\OrdersTableSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SendToTmsControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAdmin();
        $this->seed(CompaniesSeeder::class);
        Event::fake();
    }

    /** @test */
    public function it_publishs_a_message_to_sns_update_gateway()
    {
        (new OrdersTableSeeder())->seedOrderWithValidatedAddresses();
        $order = Order::first();
        $order->update([
            't_tms_provider_id' => TMSProvider::getProfitTools()->id,
            'tms_template_dictid_verified' => false,
            'carrier_dictid_verified' => false,
            'vessel_dictid_verified' => false,
        ]);
        $messageId = Str::random(5);

        $mockAction = Mockery::mock(SendOrderToTms::class)->makePartial();
        $mockAction->shouldReceive('__invoke')->andReturn(['status' => 'ok', 'message' => $messageId])->once();
        $this->app->instance(SendOrderToTms::class, $mockAction);

        $this->postJson(route('orders.send-to-tms', $order->id))
            ->assertJsonFragment(['data' => $messageId])
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function it_manages_exceptions_thrown_by_the_sns_client()
    {
        (new OrdersTableSeeder())->seedOrderWithValidatedAddresses();
        $order = Order::first();
        $order->update([
            't_tms_provider_id' => TMSProvider::getProfitTools()->id,
            'bill_to_address_verified' => false,
            'equipment_type_verified' => false,
            'tms_template_dictid_verified' => false,
            'carrier_dictid_verified' => false,
            'vessel_dictid_verified' => false,
        ]);

        $mockAction = Mockery::mock(SendOrderToTms::class)->makePartial();
        $mockAction->shouldReceive('__invoke')->andReturn(['status' => 'error', 'message' => 'exception'])->once();
        $this->app->instance(SendOrderToTms::class, $mockAction);

        $this->postJson(route('orders.send-to-tms', $order->id))
            ->assertJsonFragment(['data' => 'exception'])
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /** @test */
    public function it_should_fail_if_not_authorized_to_send_to_tms()
    {
        $this->seed(OrdersTableSeeder::class);
        $order = Order::first();
        $user = User::whereRoleIs('customer-user')->first();
        Sanctum::actingAs($user);

        $mockAction = Mockery::mock(SendOrderToTms::class)->makePartial();
        $mockAction->shouldNotReceive('__invoke');
        $this->app->instance(SendOrderToTms::class, $mockAction);

        $this->postJson(route('orders.send-to-tms', $order->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_should_fail_if_order_is_from_other_company()
    {
        $company1 = factory(Company::class)->create();
        $company2 = factory(Company::class)->create();
        $user = factory(User::class)->create(['t_company_id' => $company1->id]);
        $user->attachRole('customer-admin');
        $order = factory(Order::class)->create(['t_company_id' => $company2->id]);

        Sanctum::actingAs($user);

        $mockAction = Mockery::mock(SendOrderToTms::class)->makePartial();
        $mockAction->shouldNotReceive('__invoke');
        $this->app->instance(SendOrderToTms::class, $mockAction);

        $this->postJson(route('orders.send-to-tms', $order->id))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }
}
