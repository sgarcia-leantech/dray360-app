<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Order;
use App\Models\Address;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'request_id' => null, // We should not create ocr requests, since it's handled by the trigger inside the t_job_state_changes table
        'shipment_designation' => Arr::random(['Export', 'Import', 'Domestic']),
        'equipment_type_raw_text' => Arr::random(['Container', 'Trailer']),
        'shipment_direction' => Arr::random(['Export', 'Import', 'Domestic']),
        'one_way' => $faker->boolean,
        'unit_number' => Str::random(11),
        'equipment_size' => Arr::random(['20 ft', '40 ft', '45 ft', '48ft']),
        'hazardous' => null, // this should roll up from the line item?
        'reference_number' => $faker->lexify(),
        'seal_number' => $faker->bothify('?#######'),
        'vessel' => $faker->firstNameFemale(),
        'voyage' => $faker->numerify('###').strtoupper($faker->lexify('???')),
        'master_bol_mawb' => Arr::random([null, strtoupper($faker->bothify('??????#####????#'))]),
        'house_bol_hawb' => Arr::random([null, strtoupper($faker->bothify('??????#####????#'))]),
        'booking_number' => null,
        'bill_of_lading' => null,
        'bill_to_address_id' => $faker->boolean ? factory(Address::class) : null,
        'port_ramp_of_origin_address_id' => $faker->boolean ? factory(Address::class) : null,
        'port_ramp_of_destination_address_id' => $faker->boolean ? factory(Address::class) : null,
        'ocr_data' => json_encode([$faker->word]),
        'pickup_number' => null,
        'pickup_by_date' => null,
        'pickup_by_time' => null,
        'cutoff_date' => null,
        'cutoff_time' => null,
        'bill_to_address_verified' => $faker->boolean,
        'bill_to_address_raw_text' => $faker->address,
        'port_ramp_of_origin_address_verified' => $faker->boolean,
        'port_ramp_of_origin_address_raw_text' => $faker->address,
        'port_ramp_of_destination_address_verified' => $faker->boolean,
        'port_ramp_of_destination_address_raw_text' => $faker->address,
        'variant_id' => null,
        'variant_name' => $faker->word,
        't_tms_provider_id' => null,
        'tms_shipment_id' => null,
        'carrier' => $faker->company,
        'bill_charge' => $faker->randomFloat(2),
        'bill_comment' => null,
        'line_haul' => $faker->randomFloat(2),
        'rate_box' => null,
        'fuel_surcharge' => $faker->randomFloat(2),
        'total_accessorial_charges' => $faker->randomFloat(2),
        'equipment_provider' => null,
        'actual_destination' => null,
        'actual_origin' => null,
        'customer_number' => null,
        'expedite' => $faker->boolean,
        'load_number' => null,
        'purchase_order_number' => null,
        'release_number' => null,
        'ship_comment' => null,
        'division_code' => '2202',
        't_company_id' => null,
        'preceded_by_order_id' => null,
        'succeded_by_order_id' => null,
        'tms_submission_datetime' => null,
        'tms_cancelled_datetime' => null,
        'interchange_count' => null,
        'interchange_err_count' => null,
        'tms_template_id' => 123,
        'tms_template_dictid' => null,
        'tms_template_dictid_verified' => false,
    ];
});
