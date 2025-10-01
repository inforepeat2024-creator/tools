<?php

namespace RepeatToolkit\Helpers\StaticHelpers;

class CsvHelper
{


    public static function afterCsvCreate($path)
    {
        $f = ($path);
        $rows = file($f);



        $string = str_replace(array("\n", "\r"), '', $rows[count($rows) - 1]);

        //$string  = str_replace('\r\n', "", $rows[count($rows) - 1]);
        $rows[count($rows) - 1]  = $string;


        //  dd($rows);

        //  array_pop($rows);                       // remove final element/row from $array
        file_put_contents($f, implode($rows));

    }


    public static function readCSV($csvFile, $delimiter = ',')
    {
        $file_handle = fopen($csvFile, 'r');
        while ($csvRow = fgetcsv($file_handle, null, $delimiter)) {
            $line_of_text[] = $csvRow;
        }
        fclose($file_handle);
        return $line_of_text;
    }

}
