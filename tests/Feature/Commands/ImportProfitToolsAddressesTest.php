<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\Company;
use App\Services\Apis\RipCms;
use ProfitToolsCompaniesSeeder;
use Illuminate\Support\Facades\Http;
use App\Models\CompanyAddressTMSCode;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ImportProfitToolsAddress;
use Tests\Seeds\ProfitToolsCushingAddressesSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ImportProfitToolsAddressesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(ProfitToolsCompaniesSeeder::class);
    }

    /** @test */
    public function it_should_queue_a_job_for_each_address_from_the_endpoint()
    {
        Queue::fake();
        $this->clearTokenCache();
        Http::fakeSequence()
            ->push(['access_token' => 'test'])
            ->push([
                [ "id" => 1, "name" => "UPG3   Z 6"],
                ["id" => 2, "name" => "WSI WAREHOUSE"],
            ])
            ->push(['access_token' => 'test2'])
            ->push([
                [ "id" => 23, "name" => "UPG3   Z 6"],
                ["id" => 24, "name" => "WSI WAREHOUSE"],
            ])
            // ->whenEmpty([]);  # this didn't work
            ->push([])  # need to push one empty array for every company we address-sync,
            ->push([]); # currently there are four total (10/6/20) so we have these two. TODO fix this hack!

        $this->artisan('import:profit-tools-addresses')->assertExitCode(0);

        Queue::assertPushed(ImportProfitToolsAddress::class, 4);
        Queue::assertPushedOn(
            'imports',
            ImportProfitToolsAddress::class,
            function (ImportProfitToolsAddress $job) {
                return in_array($job->addressCode, [1, 2, 23, 24]);
            }
        );
    }

    /** @test */
    public function it_should_only_queue_the_job_for_the_companies_addresses_that_doesnt_exist()
    {
        $this->seed(ProfitToolsCushingAddressesSeeder::class);
        Queue::fake();
        $cushing = Company::getCushing();
        Cache::forget(RipCms::getTokenCacheKeyFor($cushing));
        Http::fake([
            'https://www.ripcms.com/token*' => Http::response(['access_token' => 'test']),
            'https://www.ripcms.com/api/*' => Http::response([
                [ "id" => 1, "name" => "UPG3   Z 6"],
                ["id" => 2, "name" => "WSI WAREHOUSE"],
            ]),
        ]);

        $this->artisan('import:profit-tools-addresses', [
            '--insert-only' => true,
            '--company-name' => Company::getCushing()->name,
        ])->assertExitCode(0);

        Queue::assertPushed(ImportProfitToolsAddress::class, 1);
        Queue::assertPushedOn(
            'imports',
            ImportProfitToolsAddress::class,
            fn (ImportProfitToolsAddress $job) => $job->addressCode == 2
        );
    }

    /** @test */
    public function it_should_delete_the_addresses_that_doesnt_come_in_the_api_response()
    {
        $this->seed(ProfitToolsCushingAddressesSeeder::class);
        Queue::fake();
        $cushing = Company::getCushing();
        Cache::forget(RipCms::getTokenCacheKeyFor($cushing));
        Http::fake([
            'https://www.ripcms.com/token*' => Http::response(['access_token' => 'test']),
            'https://www.ripcms.com/api/*' => Http::response([
                ["id" => 2, "name" => "WSI WAREHOUSE"],
            ]),
        ]);
        $companyAddress = CompanyAddressTMSCode::with('address:id')->first();
        $anotherCompany = factory(CompanyAddressTMSCode::class)->create();

        $this->artisan('import:profit-tools-addresses', [
            '--company-name' => Company::getCushing()->name,
        ])->assertExitCode(0);

        $this->assertSoftDeleted($companyAddress);
        $this->assertSoftDeleted($companyAddress->address);
        $anotherCompany->fresh(['address']);
        $this->assertNull($anotherCompany->deleted_at);
        $this->assertNull($anotherCompany->address->deleted_at);
    }

    /** @test */
    public function it_should_fail_if_the_rip_cms_returns_a_plain_text_with_200_code()
    {
        $this->seed(ProfitToolsCushingAddressesSeeder::class);
        Queue::fake();
        $cushing = Company::getCushing();
        Cache::forget(RipCms::getTokenCacheKeyFor($cushing));
        Http::fake([
            'https://www.ripcms.com/token*' => Http::response(['access_token' => 'test']),
            'https://www.ripcms.com/api/*' => Http::response('Some bad error handling returning 200'),
        ]);
        $companyAddress = CompanyAddressTMSCode::with('address:id')->first();
        $anotherCompany = factory(CompanyAddressTMSCode::class)->create();

        $this->artisan('import:profit-tools-addresses', [
            '--company-name' => Company::getCushing()->name,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('t_company_address_tms_code', ['id' => $companyAddress->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('t_addresses', ['id' => $companyAddress->address->id, 'deleted_at' => null]);
    }

    /** @test */
    public function it_should_fail_if_the_list_of_addresses_is_empty()
    {
        $this->seed(ProfitToolsCushingAddressesSeeder::class);
        Queue::fake();
        $cushing = Company::getCushing();
        Cache::forget(RipCms::getTokenCacheKeyFor($cushing));
        Http::fake([
            'https://www.ripcms.com/token*' => Http::response(['access_token' => 'test']),
            'https://www.ripcms.com/api/*' => Http::response([]),
        ]);
        $companyAddress = CompanyAddressTMSCode::with('address:id')->first();
        $anotherCompany = factory(CompanyAddressTMSCode::class)->create();

        $this->artisan('import:profit-tools-addresses', [
            '--company-name' => Company::getCushing()->name,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('t_company_address_tms_code', ['id' => $companyAddress->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('t_addresses', ['id' => $companyAddress->address->id, 'deleted_at' => null]);
    }

    protected function clearTokenCache()
    {
        collect([
            Company::getCushing(),
            Company::getTCompaniesDemo(),
        ])->each(function ($company) {
            Cache::forget(RipCms::getTokenCacheKeyFor($company));
        });
    }
}
