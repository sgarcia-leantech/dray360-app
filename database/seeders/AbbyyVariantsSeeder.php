<?php

namespace Database\Seeders;

/**
 * Usage: php artisan db:seed --class=OCRVariantsSeeder
 */

use App\Models\OCRVariant;
use Illuminate\Database\Seeder;

/**
 * SQLITE3 CHEATSHEET
 *
 *    sqlite3
 *     .open abbyexportfile.sqlite
 *     .tables
 *     .schema tablename
 *     .header on
 *     select * from tablename limit 10;
 */




/**
 * TerminalSeeder builds tables from Abbyy's sqllite3 database extract of all variants
 *
 * t_ocrvariants
 * =============
 * id
 * abbyy_variant_id
 * abbyy_variant_name
 * shipment_type
 * zero
 */
class AbbyyVariantsSeeder extends Seeder
{
    const INPUT_FILES = [
        [
            "FILENAME" => 'database/seeders/abbyy_variants_20210122.csv'
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::INPUT_FILES as $inputfile) {
            $filename = $inputfile['FILENAME'];
            $variantsData = $this->getCsvData($filename);
            $rowCount = count($variantsData);

            foreach ($variantsData as $csvRow) {
                $variantRow = $this->getVariantRowFromCsvRow($csvRow);
                $variantLookup = [
                    # 'abbyy_variant_id' => $variantRow['abbyy_variant_id'], # don't lookup on abbyy_variant_id
                    'abbyy_variant_name' => $variantRow['abbyy_variant_name'],
                ];
                $rownum = $variantRow['rownum'];
                unset($variantRow['rownum']);

                $x = OCRVariant::firstOrCreate($variantLookup, $variantRow);  // if we wanted to update, we could use updateOrCreate()
                $status = 'already existed ';
                if ($x->wasRecentlyCreated) {
                    $status = 'created ';
                }
                // happy message, return
                $msg = $status.'csvfile row:'.$rownum.'/'.$rowCount.' for variant: "'.$variantRow['abbyy_variant_name'].'" as t_ocrvariants.id: '.$variantRow['abbyy_variant_id'];
                $this->command->info($msg);
            }
        }
    }

    /**
     * Load the CSV file, return array. Note that this is shameless
     * copy/pasta from the EquipmentLeaseTypeSeeder.
     *
     * TODO: make this a  shared library somewhere.
     *
     * @param string $filename name of csv file
     *
     * @return array
     */
    public function getCsvData($filename)
    {
        $this->command->info('');
        $this->command->info('');
        $this->command->info('');
        $this->command->info('reading: '.$filename);
        $this->command->info('');

        $alldata = [];
        if (($handle = fopen($filename, "r")) !== false) {
            $headers = null;
            $rownum = -1;
            while (($data = fgetcsv($handle)) !== false) {
                $rownum++;
                if ($rownum == 0) {
                    $headers = $data;
                } else {
                    $rowData = ['rownum' => $rownum];
                    $fieldcount = count($data);
                    for ($i = 0; $i < $fieldcount; $i++) {
                        $colname = $headers[$i];
                        $coldata = $data[$i];
                        $rowData[$colname] = $coldata;
                    }
                    array_push($alldata, $rowData);
                }
            }
            fclose($handle);
        }
        return $alldata;
    }

    /**
     * Parse one row of ABBYY's CSV variants data
     * Assumes file format: "id","abbyy_variant_id","abbyy_variant_name","shipment_type","zero"
     *
     * @param $csvRow full row data
     *
     * @return EquipmentType
     */
    public function getVariantRowFromCsvRow($csvRow)
    {
        return ([
            'abbyy_variant_id' => $csvRow['abbyy_variant_id'],
            'abbyy_variant_name' => $csvRow['abbyy_variant_name'],
            'variant_type' => 'ocr',  // abbyy variants are always type 'ocr'
            'description' => 'imported from Abbyy',
            'rownum' => $csvRow['rownum'],
        ]);
    }
}
