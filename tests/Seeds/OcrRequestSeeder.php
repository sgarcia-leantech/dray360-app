<?php

namespace Tests\Seeds;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Company;
use Illuminate\Database\Seeder;
use App\Models\OCRRequestStatus;
use Illuminate\Support\Facades\DB;

class OcrRequestSeeder extends Seeder
{
    /**
     * Make an order having a related OCR request, and order line items, etc.
     */
    public function run(): void
    {
        $this->seedOcrJob_intakeRejected();
        $this->seedOcrJob_ocrPostProcessingComplete();
        $this->seedOcrJob_ocrWaiting();
    }

    //
    // "flow" replicated from: bab69a29-80f4-51c4-a3f0-cc4d3cc8b6a5
    // create state #1: intake-started
    // create state #2: ocr-waiting
    // create state #3: ocr-completed
    // create state #4: process-ocr-output-file-complete
    // create state #5: ocr-post-processing-complete
    //

    public function seedOcrJob_ocrPostProcessingComplete(): string
    {
        // echo('Creating OCR job with status=ocr-post-processing-complete'.PHP_EOL);
        $faker = \Faker\Factory::create();

        // request_id must be shared by all states, and resulting order
        $request_id = $faker->uuid;
        $company = factory(Company::class)->create();

        // handy variables
        $time5MinutesAgo = Carbon::now()->subMinutes(5)->toDateTimeString();
        $time4MinutesAgo = Carbon::now()->subMinutes(4)->toDateTimeString();
        $time3MinutesAgo = Carbon::now()->subMinutes(3)->toDateTimeString();
        $time2MinutesAgo = Carbon::now()->subMinutes(2)->toDateTimeString();
        $time1MinutesAgo = Carbon::now()->subMinutes(1)->toDateTimeString();

        // create state #1: intake-started
        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time5MinutesAgo,
            'status' => OCRRequestStatus::INTAKE_STARTED,
            'status_metadata' => '{"event_info": {"event_time": "2019-12-06T20:28:59.595Z", "object_key": "intakeemail/4tckssjbuh0c2dt8rlund3efvcd4g6pmjeagee81", "bucket_name": "dmedocproc-emailintake-dev", "aws_request_id": "'.$request_id.'", "log_group_name": "/aws/lambda/intake-filter-dev", "log_stream_name": "2019/12/06/[$LATEST]55e4fa95494f4364a68a85e537e8e3fa", "event_time_epoch_ms": 1575664139000}, "request_id": "'.$request_id.'", "source_summary": {"source_type": "email", "source_email_subject": "Fwd: test 202", "source_email_to_address": "dev@docprocessing.draymaster.com", "source_email_from_address": "Peter Nelson <peter@peternelson.com>", "source_email_body_prefixes": ["b\'---------- Forwarded message ---------\\r\\nFrom: Peter Nelson <peter@peternelson.com>\\r\\nDate: Fri, Dec 6, 2019 at 1:43 PM\\r\\nSubject: test 202\\r\\nTo: Peter B. Nelson <peter@peternelson.com>\\r\\n\'", "b\'<div dir=\"ltr\"><div class=\"gmail_default\" style=\"font-size:small\"><br></div><br><div class=\"gmail_quote\"><div dir=\"ltr\" class=\"gmail_attr\">---------- Forwarded message ---------<br>From: <b class=\"gmail_sendername\" dir=\"auto\">Peter Nelson</b> <span dir=\"auto\">&lt;<a href=\"mailto:peter@peternelson.com\">peter@peternelson.com</a>&gt;</span><br>Date: Fri, Dec 6, 2019 at 1:43 PM<br>Subject: test 202<br>To: Peter B. Nelson &lt;<a href=\"mailto:peter@peternelson.com\">peter@peternelson.com</a>&gt;<br><"], "source_email_string_length": 164489, "source_email_attachment_filenames": ["MATSON-examplar.pdf"]}, "read_log_commandline": "aws --profile=draymaster logs get-log-events --log-group-name=\'/aws/lambda/intake-filter-dev\' --log-stream-name=\'2019/12/06/[$LATEST]55e4fa95494f4364a68a85e537e8e3fa\' --start-time=\'1575664139000\'"}',
        ]);

        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time5MinutesAgo,
            'status' => OCRRequestStatus::INTAKE_ACCEPTED,
            'status_metadata' => '{"document_type": "pdf", "document_filename": "1fa83bf8-3c64-5db5-a12e-6c96dc61269d_9f34ffd1b9ba31db17de0b21d6f4028f7f4191ac170ae9ee53dd86f3f7cb3529_ShipmentCartageAdviceWithReceipt-SSI100072107.PDF", "original_filename": "ShipmentCartageAdviceWithReceipt-SSI100072107.PDF", "document_archive_location": "s3://dmedocproc-emailintake-dev/intakearchive/1fa83bf8-3c64-5db5-a12e-6c96dc61269d_9f34ffd1b9ba31db17de0b21d6f4028f7f4191ac170ae9ee53dd86f3f7cb3529_ShipmentCartageAdviceWithReceipt-SSI100072107.PDF"}',
        ]);

        // create state #2: ocr-waiting
        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time4MinutesAgo,
            'status' => OCRRequestStatus::OCR_WAITING,
            'status_metadata' => '{"wait_reason": "WorkflowException", "exception_message": "No files found matching '.$request_id.'*.csv"}',
        ]);

        // create state #3: ocr-completed
        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time3MinutesAgo,
            'status' => OCRRequestStatus::OCR_COMPLETED,
            'status_metadata' => '{"file_list": ["'.$request_id.'_MATSON-examplar_00000001.csv"], "s3_bucket": "dmedocproc-processedjobs-dev", "s3_region": "us-east-2"}',
        ]);

        // create state #4: process-ocr-output-file-complete
        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time2MinutesAgo,
            'status' => OCRRequestStatus::PROCESS_OCR_OUTPUT_FILE_COMPLETE,
            'status_metadata' => '{"filename": "'.$request_id.'_MATSON-examplar_00000001.csv", "row_count": 1}',
        ]);

        // create state #5: ocr-post-processing-complete
        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time1MinutesAgo,
            'status' => OCRRequestStatus::OCR_POST_PROCESSING_COMPLETE,
            'status_metadata' => '{"num_files_to_process": 1, "num_files_processed_successfully": 1, "num_files_processed_unsuccessfully": 0}',
        ]);

        // all done, return request_id needed to create an order
        return $request_id;
    }

    //
    // "flow" replicated from: f9983481-87f6-5b57-b779-62e2247a6db7
    // create state #1: intake-started
    // create state #2: intake-rejected
    //

    public function seedOcrJob_intakeRejected(): string
    {
        // echo('Creating OCR job status=intake-rejected'.PHP_EOL);
        $faker = \Faker\Factory::create();

        // request_id must be shared by all states, and resulting order
        $request_id = $faker->uuid;
        $company = factory(Company::class)->create();

        // handy variables
        $time5MinutesAgo = Carbon::now()->subMinutes(5)->toDateTimeString();
        $time4MinutesAgo = Carbon::now()->subMinutes(4)->toDateTimeString();

        // create state #1: intake-started
        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time5MinutesAgo,
            'status' => OCRRequestStatus::INTAKE_STARTED,
            'status_metadata' => '{"event_info": {"event_time": "2019-12-06T00:31:12.873Z", "object_key": "intakeemail/3ii4d5gekodc5mnmdaqim08ft445k3aodf3laqo1", "bucket_name": "dmedocproc-emailintake-dev", "aws_request_id": "'.$request_id.'", "log_group_name": "/aws/lambda/intake-filter-dev", "log_stream_name": "2019/12/06/[$LATEST]5fdbcbb1b8e24ee0afa6cc506b24b387", "event_time_epoch_ms": 1575592272000}, "request_id": "'.$request_id.'", "source_summary": {"source_type": "email", "source_email_subject": "test193", "source_email_to_address": "dev@docprocessing.draymaster.com", "source_email_from_address": "Peter Nelson <peter@peternelson.com>", "source_email_body_prefixes": ["b\'\\r\\n\'\", \"b\'<div dir=\"ltr\"><div class=\"gmail_default\" style=\"font-size:small\"><br></div></div>\\r\\n\'"], "source_email_string_length": 233429, "source_email_attachment_filenames": ["cai-logistics-pg1.pdf", "cai-logistics-pg2.pdf", "cai-logistics-pg3.pdf"]}}',
        ]);

        // create state #2: intake-rejected
        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time4MinutesAgo,
            'status' => OCRRequestStatus::INTAKE_REJECTED,
            'status_metadata' => '{"rejection_reason": "WorkflowException", "exception_message": "Ambiguous attachments in this email. Attachments found: [\'cai-logistics-pg1.pdf\', \'cai-logistics-pg2.pdf\', \'cai-logistics-pg3.pdf\']"}',
        ]);

        // all done, return request_id needed to create an order
        return $request_id;
    }

    public function seedOcrJob_ocrWaiting(): string
    {
        // echo('Creating OCR job status=intake-rejected'.PHP_EOL);
        $faker = \Faker\Factory::create();

        // request_id must be shared by all states, and resulting order
        $request_id = $faker->uuid;
        $company = factory(Company::class)->create();

        // handy variables
        $time5MinutesAgo = Carbon::now()->subMinutes(5)->toDateTimeString();
        $time4MinutesAgo = Carbon::now()->subMinutes(4)->toDateTimeString();

        // create state #1: intake-started
        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time5MinutesAgo,
            'status' => OCRRequestStatus::INTAKE_STARTED,
            'status_metadata' => '{"event_info": {"event_time": "2019-12-06T20:28:59.595Z", "object_key": "intakeemail/4tckssjbuh0c2dt8rlund3efvcd4g6pmjeagee81", "bucket_name": "dmedocproc-emailintake-dev", "aws_request_id": "'.$request_id.'", "log_group_name": "/aws/lambda/intake-filter-dev", "log_stream_name": "2019/12/06/[$LATEST]55e4fa95494f4364a68a85e537e8e3fa", "event_time_epoch_ms": 1575664139000}, "request_id": "'.$request_id.'", "source_summary": {"source_type": "email", "source_email_subject": "Fwd: test 202", "source_email_to_address": "dev@docprocessing.draymaster.com", "source_email_from_address": "Peter Nelson <peter@peternelson.com>", "source_email_body_prefixes": ["b\'---------- Forwarded message ---------\\r\\nFrom: Peter Nelson <peter@peternelson.com>\\r\\nDate: Fri, Dec 6, 2019 at 1:43 PM\\r\\nSubject: test 202\\r\\nTo: Peter B. Nelson <peter@peternelson.com>\\r\\n\'", "b\'<div dir=\"ltr\"><div class=\"gmail_default\" style=\"font-size:small\"><br></div><br><div class=\"gmail_quote\"><div dir=\"ltr\" class=\"gmail_attr\">---------- Forwarded message ---------<br>From: <b class=\"gmail_sendername\" dir=\"auto\">Peter Nelson</b> <span dir=\"auto\">&lt;<a href=\"mailto:peter@peternelson.com\">peter@peternelson.com</a>&gt;</span><br>Date: Fri, Dec 6, 2019 at 1:43 PM<br>Subject: test 202<br>To: Peter B. Nelson &lt;<a href=\"mailto:peter@peternelson.com\">peter@peternelson.com</a>&gt;<br><"], "source_email_string_length": 164489, "source_email_attachment_filenames": ["MATSON-examplar.pdf"]}, "read_log_commandline": "aws --profile=draymaster logs get-log-events --log-group-name=\'/aws/lambda/intake-filter-dev\' --log-stream-name=\'2019/12/06/[$LATEST]55e4fa95494f4364a68a85e537e8e3fa\' --start-time=\'1575664139000\'"}',
        ]);

        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time5MinutesAgo,
            'status' => OCRRequestStatus::INTAKE_ACCEPTED,
            'status_metadata' => '{"document_type": "pdf", "document_filename": "1fa83bf8-3c64-5db5-a12e-6c96dc61269d_9f34ffd1b9ba31db17de0b21d6f4028f7f4191ac170ae9ee53dd86f3f7cb3529_ShipmentCartageAdviceWithReceipt-SSI100072107.PDF", "original_filename": "ShipmentCartageAdviceWithReceipt-SSI100072107.PDF", "document_archive_location": "s3://dmedocproc-emailintake-dev/intakearchive/1fa83bf8-3c64-5db5-a12e-6c96dc61269d_9f34ffd1b9ba31db17de0b21d6f4028f7f4191ac170ae9ee53dd86f3f7cb3529_ShipmentCartageAdviceWithReceipt-SSI100072107.PDF"}',
        ]);

        // create state #2: ocr-waiting
        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time4MinutesAgo,
            'status' => OCRRequestStatus::OCR_WAITING,
            'status_metadata' => '{"wait_reason": "WorkflowException", "exception_message": "No files found matching '.$request_id.'*.csv"}',
        ]);

        // all done, return request_id needed to create an order
        return $request_id;
    }

    public function seedPtImageUploadSucceeded(): string
    {
        // echo('Creating OCR job status=intake-rejected'.PHP_EOL);
        $faker = \Faker\Factory::create();

        // request_id must be shared by all states, and resulting order
        $request_id = $faker->uuid;
        $company = factory(Company::class)->create();

        // handy variables
        $time5MinutesAgo = Carbon::now()->subMinutes(5)->toDateTimeString();
        $time4MinutesAgo = Carbon::now()->subMinutes(4)->toDateTimeString();

        // create state #1: intake-started
        $user = factory(User::class)->create();
        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time5MinutesAgo,
            'status' => OCRRequestStatus::UPLOAD_IMAGE_REQUESTED,
            'status_metadata' => '{"user_id":'.$user->id.',"order_id":null,"company_id":2,"request_id":"671a7bc3-bb7a-4fcf-80f7-8c2182d965d2","pt_image_type":"PRE-NOTE","s3_object_key":"imageupload\/671a7bc3-bb7a-4fcf-80f7-8c2182d965d2.00000001_1.jpg.apiupload","s3_bucket_name":"dray360-emailintake-dev","datetime_utciso":"2021-04-20T16:31:45.159048Z","tms_provider_id":1,"tms_shipment_id":"3937","original_filename":"00000001_1.jpg"}',
        ]);

        DB::table('t_job_state_changes')->insert([
            'request_id' => $request_id,
            'company_id' => $company->id,
            'status_date' => $time5MinutesAgo,
            'status' => OCRRequestStatus::UPLOAD_IMAGE_SUCCEEDED,
            'status_metadata' => '{"message":"success uploading image to profit tools","event_info":{"s3_uri":"s3:\/\/dray360-emailintake-dev\/imageupload\/671a7bc3-bb7a-4fcf-80f7-8c2182d965d2.00000001_1.jpg.apiupload","status":"upload-image-requested","user_id":'.$user->id.',"order_id":" ","company_id":"2","request_id":"671a7bc3-bb7a-4fcf-80f7-8c2182d965d2","pt_image_type":"PRE-NOTE","s3_object_key":"imageupload\/671a7bc3-bb7a-4fcf-80f7-8c2182d965d2.00000001_1.jpg.apiupload","s3_bucket_name":"dray360-emailintake-dev","tms_provider_id":1,"tms_shipment_id":"3937","original_filename":"00000001_1.jpg"},"datetime_utciso":"2021-04-20T16:31:50.298378Z"}',
        ]);

        // all done, return request_id needed to create an order
        return $request_id;
    }
}
