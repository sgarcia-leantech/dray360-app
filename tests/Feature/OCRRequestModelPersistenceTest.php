<?php

// to use: ./vendor/bin/phpunit --filter OCRRequestModelPersistenceTest



namespace Tests\Feature;

#use Illuminate\Foundation\Testing\RefreshDatabase;
#use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;



class OCRRequestModelPersistenceTest extends TestCase
{

    /**
     * Set up in-memory database instance for testing
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed', ['--class' => 'OrdersTableSeeder']);
    }

    /**
     * Tear down in-memory unit testing instance
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }



    /**
     * Order object can be retrieved and has correct relations to
     * OCRRequest object and OCRRequestStatus object list.
     *
     * @return void
     * @test
     */
    public function orderObjectAndOCRRequestRelations()
    {
        // 1. should be able to retrieve an order (should have been seeded)
        $o1 = \App\Models\Order::all()->random();
        $this->assertNotNull($o1, 'Expect Order to not be null');

        // 2. should be able to get the (seeded) request_id from that order
        $request_id = $o1['request_id'];
        $this->assertNotNull($request_id, 'Expect Order to have a request_id');

        // 3. should be able to get the OCRRequest object having that same request id
        $r1=\App\Models\OCRRequest::where('request_id', $request_id)->get()[0];
        $this->assertNotNull($r1, 'Expect OCRRequest to exist having same request_id as Order');

        // 4. that particular OCR Request should have the given order in its orders list
        $r1OrderList = $r1->orders()->get();
        $foundOrder = false;
        foreach ($r1OrderList as $eachOrder) {
            echo $eachOrder->request_id;
            if ($o1 == $eachOrder) { // cannot use ===
                $foundOrder = true;
                break;
            }
        }
        $this->assertTrue($foundOrder, 'Expect OCRRequest to include Order having same request_id');

        // 5. should be able to get all the OCRRequestStatus objects having that same id
        $s1=\App\Models\OCRRequestStatus::where('request_id', $request_id)->get();
        $this->assertNotNull($s1, 'Expect OCRRequestStatus entries for that request_id');

        // 6. get the OCRRequest off one of the OCRRequestStatus objects,
        // which should match the originally retrieved OCRRequest object
        $s1ocrRequest = $s1[0]->ocrRequest()->get()[0];
        $this->assertEquals($r1, $s1ocrRequest, 'OCRRequest retrieved directly should match the one retrieved indirectly from OCRRequestStatus');

        // 7. One and only one of those OCRRequestStatus objects should be "is_latest_status"
        $is_latest_sum = 0;
        $r1statusList = $r1->statusList()->get();
        foreach ($r1statusList as $eachStatus) {
            $is_latest_sum += $eachStatus['is_latest_status'];
        }
        $this->assertEquals($is_latest_sum, 1, 'One and only one OCRRequestStatus should be flagged as "is_latest_status"');
    }

}