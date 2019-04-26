<?php
/**
 * Created by PhpStorm.
 * User: deven
 * Date: 22/03/2016
 * Time: 5:01 PM
 */



use Box\Spout\Common\Type;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Writer\WriterFactory;

class NeonExcelIO
{


    var $file ;
    var $Sheet ;
    var $first_row ; // Read: skipp first row for column name
    var $sheet ; // default sheet to read
    var $row_cnt = 0; // set row counter to 0
    var $columns = array();
    var $file_type;
    var $records; // output records
    var $reader;
    var $Delimiter;
    var $Enclosure;
    var $Escape;
    var $csvoption;
    public static $COLUMN_NAMES 	= 	0 ;
    public static $start_row 	= 	0 ;
    public static $end_row 	= 	0 ;
    public static $DATA    			= 	1;
    public static $EXCEL   			= 	'xlsx'; // Excel file
    public static $EXCELs  			= 	'xls'; // Excel file
    public static $CSV 	   			= 	'csv'; // csv file


    public function __construct($file , $csvoption = array(), $Sheet='')
    {
        $this->file = $file;
        $this->Sheet = $Sheet;
        $this->sheet = 0;
        $this->first_row = self::$COLUMN_NAMES;
        $this->file_type = self::$CSV;
        $this->set_file_type();
        $this->get_file_settings($csvoption);
        /*if(self::$start_row>0)
        {
            self::$start_row--;
        }*/
    }


    public function get_file_settings($csvoption=array()) {

        if(!empty($csvoption)){

            if(!empty($csvoption["Delimiter"])){
                $this->Delimiter = $csvoption["Delimiter"];

            }
            if(!empty($csvoption["Enclosure"])){
                $this->Enclosure = $csvoption["Enclosure"];

            }
            if(!empty($csvoption["Enclosure"])){

                $this->Escape    = $csvoption["Enclosure"];
            }
            if(!empty($csvoption["Firstrow"]) && $csvoption["Firstrow"] == 'data'){

                $this->first_row = self::$DATA;
            }else{

                $this->first_row = self::$COLUMN_NAMES;

            }

        }

    }

    public function set_file_settings() {

        if(!empty($this->Delimiter)) {
            $this->reader->setFieldDelimiter($this->Delimiter);
        }
        if(!empty($this->Enclosure)) {
            $this->reader->setFieldEnclosure($this->Enclosure);
        }
        if(!empty($this->Escape)) {
            $this->reader->setEndOfLineCharacter($this->Escape);
        }
    }


    public function set_file_type(){

        $extension = pathinfo($this->file,PATHINFO_EXTENSION);

        if(in_array($extension ,["xls","xlsx"])){
            $this->set_file_excel();
        }else{
            $this->set_file_csv();
        }

    }

    public function set_file_excel(){
		$extension = pathinfo($this->file,PATHINFO_EXTENSION);
		if($extension=='xls'){
        	$this->file_type = self::$EXCELs;
		}
		if($extension=='xlsx'){
        	$this->file_type = self::$EXCEL;
		}
    }

    public function set_file_csv(){

        $this->file_type = self::$CSV;

    }

    /** Set sheet to read / write
     * @param $sheet
     */
    public function set_sheet($sheet) {

        if(is_numeric($sheet) && $sheet >= 0 ){

            $this->sheet = $sheet;
        }
    }

    public function read($limit=0) {

        if($this->file_type == self::$CSV){
            return $this->read_csv($this->file,$limit);
        }
        if($this->file_type == self::$EXCEL){

            //return $this->read_excel($this->file,$limit);
            return $this->readExcel($this->file,$limit);
        }
		
		if($this->file_type == self::$EXCELs){

            //return $this->read_xls_excel($this->file,$limit);
            return $this->readExcel($this->file,$limit);
        }
    }

    /** Create CSV file from rows data from procedure.
     * @param $filepath
     * @param $rows
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     */
    public function write_csv($rows){

        $writer = WriterFactory::create(Type::CSV); // for XLSX files
        $writer->openToFile($this->file); // write data to a file or to a PHP stream

        if(isset($rows[0]) && count($rows[0]) > 0 ) {

            $columns = array_keys($rows[0]);
            $writer->addRow($columns); // add a row at a time
            $writer->addRows($rows); // add multiple rows at a time
        }
        $writer->close();

    }

    /** Create Excel file from rows data from procedure.
     * @param $filepath
     * @param $rows
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     */
    public function write_excel($rows){

        $writer = WriterFactory::create(Type::XLSX); // for XLSX files
        $writer->openToFile($this->file); // write data to a file or to a PHP stream

        if(isset($rows[0]) && count($rows[0]) > 0 ) {
            $columns = array_keys($rows[0]);  // Column Names
            $writer->addRow($columns); // add a row at a time
            $writer->addRows($rows); // add multiple rows at a time
        }
        $writer->close();
    }

    /** Read Excel file
     * @param $filepath
     * @return array
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     */
    public function read_csv($filepath,$limit=0) {
        if(self::$start_row>0)
        {
            if($limit>0)
            {
                $limit++;
            }
        }

        $result = array();
        $this->reader = ReaderFactory::create(Type::CSV); // for XLSX files
        $this->set_file_settings();
        $this->reader->open($filepath);
        foreach($this->reader->getSheetIterator() as $key  => $sheet) {

            // For First Sheet only.
            if($key == 1) {
                    foreach ($sheet->getRowIterator() as $row) {

                    if(self::$start_row>= ($this->row_cnt+1))
                    {
                        $this->row_cnt++;
                        if($limit>0)
                        {
                            $limit++;
                        }
                        continue;
                    }

                    if($limit > 0 && $limit <= $this->row_cnt) {
//                        break;
                        $this->row_cnt++;
                        continue;
                    }

                    if ($this->row_cnt == 0 && $this->first_row == self::$COLUMN_NAMES) {
                        $first_row = $row;
                        $this->set_columns($first_row);
                        $this->row_cnt++;
                        if($limit > 0 ){
                            $limit++;
                        }
                        continue;
                    }
                    else if( self::$start_row>0 && $this->row_cnt == self::$start_row && $this->first_row == self::$COLUMN_NAMES)
                    {
                        $first_row = $row;
                        $this->set_columns($first_row);
                        $this->row_cnt++;
                        continue;
                    }

                    $result[] = $this->set_row($row);

                    $this->row_cnt++;

                }
            }

        }

        //$result = $this->remove_footer_bottom_rows($result);

        if(self::$end_row)
        {
            $requiredRow = abs($this->row_cnt - self::$end_row - self::$start_row-1);
            $totatRow = count($result);
            if($requiredRow<$limit || $limit==0)
            {
                for($i=$requiredRow ; $i < $totatRow; $i++)
                {
                    unset($result[$i]);
                }
            }
        }

        $this->reader->close();

        if(!empty($result) && count($result)>0){
            $result = $this->utf8json($result);
        }
        
        return $result;

    }

    public function read_excel($filepath,$limit=0){

        if(self::$start_row>0)
        {
            if($limit>0)
            {
                $limit++;
            }
        }

        $this->reader = ReaderFactory::create(Type::XLSX); // for XLSX files
        $this->reader->open($filepath);
        $result = array();

        foreach($this->reader->getSheetIterator() as $key  => $sheet) {

            // For First Sheet only.
            if($key == 1) {

                foreach ($sheet->getRowIterator() as $row) {

                    if($limit > 0 && $this->row_cnt > $limit) {
                        break;
                    }

                    if(self::$start_row > ($this->row_cnt))
                    {
                        $this->row_cnt++;
                        if($limit>0) {
                            $limit++;
                        }
                        continue;
                    }

                    if($limit > 0 && $limit <= $this->row_cnt) {
//                        break;
                        $this->row_cnt++;
                        continue;
                    }

                    if ($this->row_cnt == 0 && $this->first_row == self::$COLUMN_NAMES) {
                        $first_row = $row;
                        $this->set_columns($first_row);
                        $this->row_cnt++;
                        if($limit > 0 ){
                            $limit++;
                        }
                        continue;
                    }
                    else if(self::$start_row>0 && $this->row_cnt == self::$start_row && $this->first_row == self::$COLUMN_NAMES) {
                            $first_row = $row;
                            $this->set_columns($first_row);
                            $this->row_cnt++;
                            continue;
                        }

                    $result[] = $this->set_row($row);
                    $this->row_cnt++;

                }
            }
        }

        //$result = $this->remove_footer_bottom_rows($result);

        if(self::$end_row)
        {
            $requiredRow = abs($this->row_cnt - self::$end_row - self::$start_row);
            $totatRow = count($result);
            if($requiredRow<$limit || $limit==0)
            {
                for($i=$requiredRow ; $i < $totatRow; $i++)
                {
                    unset($result[$i]);
                }
            }
        }
        $this->reader->close();

        return $result;

    }
	/*
		Read xls file
	*/
	////////
    /**
     * @param $filepath
     * @param int $limit
     * @return mixed
     */
    public function read_xls_excel($filepath, $limit=0){
		  $result = array();
		  $flag   = 0;			 
		   if (!empty($data['Delimiter'])) {
				Config::set('excel::csv.delimiter', $data['Delimiter']);
			}
			if (!empty($data['Enclosure'])) {
				Config::set('excel::csv.enclosure', $data['Enclosure']);
			}
			if (!empty($data['Escape'])) {
				Config::set('excel::csv.line_ending', $data['Escape']);
			}
			if(!empty($data['Firstrow'])){
				$data['option']['Firstrow'] = $data['Firstrow'];
			}
		
			if (!empty($data['option']['Firstrow'])) {
				if ($data['option']['Firstrow'] == 'data') {
					$flag = 1;
				}
			}
			$isExcel = in_array(pathinfo($filepath, PATHINFO_EXTENSION),['xls','xlsx'])?true:false;
            $totalRow=0;
            if($limit>0)
            {
                $limit++;
            }

			$result = Excel::selectSheetsByIndex(0)->load($filepath, function ($reader) use ($flag,$isExcel,&$totalRow) {
                if(self::$start_row>0)
                {
                    $reader->skip(self::$start_row-1);
                }
                $totalRow=$reader->getTotalRowsOfFile();
				if ($flag == 1) {
					$reader->noHeading();
				}
			})->take($limit)->toArray();

            if(self::$start_row>0)
            {
                $tmp_results=array();
                $column=array_values($result[0]);
                unset($result[0]);
                foreach ($result as $row)
                {
                        $tmp_results[] = array_combine($column, array_values($row));
                }
                $result=$tmp_results;
            }

            //$result = $this->remove_footer_bottom_rows($result);

            if(self::$end_row && $totalRow>0)
             {
                 $requiredRow = $totalRow - self::$end_row - self::$start_row;
                 $countRow =count($result);
                for($i=$requiredRow-1 ; $i < $countRow; $i++)
                {
                    unset($result[$i]);
                }
             }

         return $result;
	 }
	///////////

    /** Set Column Names from first row
     * @param $first_row
     */

    public function set_columns($first_row){

        if(is_array($first_row) && count($first_row) > 0 ){

            $this->columns = $first_row;
        }

    }

    /** Return row as associative array of columnnames
     * @param $row
     * @return array
     */
    public function set_row($row) {

        $col_row = array();

        foreach($row as $col_index => $row_value){

            $col_key = $col_index ;

            if ($this->first_row == self::$COLUMN_NAMES && isset($this->columns[$col_index]) ){
                $col_key = ($this->columns[$col_index]);
            }

            // for dat value only
            if( method_exists($row_value , "format") && $col_key instanceof DateTime ) {
                $col_row[$col_index] = $row_value->format("H:i:s") != '00:00:00' ? $row_value->format("Y-m-d H:i:s") : $row_value->format("Y-m-d");
            }elseif( method_exists($row_value , "format")) {
                $col_row[$col_key] = $row_value->format("H:i:s") != '00:00:00' ? $row_value->format("Y-m-d H:i:s") : $row_value->format("Y-m-d");
            }else{
                $col_row[$col_key] = $row_value;
            }
        }

        return $col_row;
    }

    public function download_excel($rows){
        $this->write_excel($rows);
        download_file($this->file);
    }

    public function download_csv($rows){
        $this->write_csv($rows);
        download_file($this->file);
    }

    // get utf8 result
    public function utf8json($inArray) {

        static $depth = 0;

        /* our return object */
        $newArray = array();

        /* safety recursion limit */
        /*
        $depth ++;
        if($depth >= '30') {
            return false;
        }*/

        /* step through inArray */
        foreach($inArray as $key=>$val) {
            if(is_array($val)) {
                /* recurse on array elements */
                $newArray[$key] = $this->utf8json($val);
            } else {
                /* encode string values */
                $newArray[$key] = utf8_encode($val);
            }
        }

        /* return utf8 encoded array */
        return $newArray;
    }


    /** @TODO: need to this function on endrow logic
     * Remove footer bottom rows for vendor upload file - for file cleanup.
     * @param $result
     */
    public function remove_footer_bottom_rows($result){

        if ( count($result) > 0 ) {

            Log::info("Before end row cleanup");
            Log::info("Total Result entries " . count($result));
            Log::info(print_r($result[0],true));
            Log::info(print_r($result[(count($result)-1)],true));

            $columns = array_keys($result);

            if(count($columns) > 0) {

                for($i  = count($result) - 1 ; $i > 0; $i--)
                {
                    $empty_cnt = 0;
                    foreach ($columns as $column ) {

                        if(isset($result[$i][$column]) && empty($result[$i][$column])){
                            $empty_cnt++;
                        }
                    }
                    if($empty_cnt > 2){
                        unset($result[$i]);
                    } else {
                        break;
                    }
                }
            }

            Log::info("After end row cleanup");
            Log::info("Total Result entries " . count($result));
            Log::info(print_r($result[0],true));
            Log::info(print_r($result[(count($result)-1)],true));

        }
        return $result;

    }

    public function convertExcelToCSV($data) {
        try {
            $file_name = $this->file;
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);

            if (in_array(strtolower($ext), array("xls", "xlsx"))) {
                //reading from excel file and getting data from excel file starts
                $start_time = date('Y-m-d H:i:s');
                $objPHPExcelReader = PHPExcel_IOFactory::load($file_name);

                if(!empty($this->Sheet)) {
                    $objPHPExcelReader->setActiveSheetIndexByName($this->Sheet);
                }

                $ActiveSheet = $objPHPExcelReader->getActiveSheet();
                $drow = $ActiveSheet->getHighestDataRow();
                $dcol = $ActiveSheet->getHighestDataColumn();

                $start_row = intval($data["start_row"]) + 1;
                $end_row = ($drow - intval($data["end_row"]));

                Log::info('start row : ' . $start_row);
                Log::info('highest row : ' . $drow . ' and highest col : ' . $dcol);

                $start_time1 = date('Y-m-d H:i:s');
                $allRows = $ActiveSheet->rangeToArray('A' . $start_row . ':' . $dcol . $end_row);
                //print_r($allRows);
                $end_time1 = date('Y-m-d H:i:s');
                $process_time1 = strtotime($end_time1) - strtotime($start_time1);
                Log::info('rangeToArray function call time : ' . $process_time1 . ' Seconds');

                //Log::info(print_r(array_slice($allRows,0,10),true));

                $file_name = substr($file_name, 0, strrpos($file_name, '.')) .'_'.$this->Sheet.'.csv';
                $end_time = date('Y-m-d H:i:s');
                $process_time = strtotime($end_time) - strtotime($start_time);
                Log::info('Convert to csv read time : ' . $process_time . ' Seconds');
                //reading from excel file and getting data from excel file ends

                $header_rows = $footer_rows = array();
                $char_arr = array_combine(range('a','z'),range(1,26));

                if ($start_row > 0) {
                    for ($i = 0; $i < intval($data["start_row"]); $i++) {
                        $row = array();
                        for ($j = 0; $j <= $char_arr[strtolower($dcol)] - 1; $j++) {
                            $row[$j] = "";
                        }
                        $header_rows[$i] = $row;
                    }
                }
                if (intval($data["end_row"]) > 0) {
                    for ($i = 0; $i < intval($data["end_row"]); $i++) {
                        $row = array();
                        for ($j = 0; $j <= $char_arr[strtolower($dcol)] - 2; $j++) {
                            $row[$j] = "";
                        }
                        $footer_rows[$i] = $row;
                    }
                }

                // creating csv file starts
                $start_time = date('Y-m-d H:i:s');
                $writer = WriterFactory::create('csv');
                $writer->openToFile($file_name);
                $writer->addRows($header_rows);
                $writer->addRows($allRows);
                $writer->addRows($footer_rows);
                $writer->close();
                $end_time = date('Y-m-d H:i:s');
                $process_time = strtotime($end_time) - strtotime($start_time);
                Log::info('Convert to csv using PHPExcel : ' . $process_time . ' Seconds');
                // creating csv file ends

                return $file_name;
            } else {
                return $file_name;
            }
        } catch (Exception $e) {
            return Response::json(array("status" => "failed", "message" => $e->getMessage()));
        }
    }

    //same function in service when change this need to change in service too
    public function readExcel($filepath,$limit=0) {
        $start_time = date('Y-m-d H:i:s');

        $start_time1 = date('Y-m-d H:i:s');
        $objPHPExcelReader = PHPExcel_IOFactory::load($filepath);
        $end_time1 = date('Y-m-d H:i:s');
        $process_time1 = strtotime($end_time1) - strtotime($start_time1);
        Log::info('load function call time : ' . $process_time1 . ' Seconds');

        $start_time1 = date('Y-m-d H:i:s');
        if(!empty($this->Sheet)) {
            $objPHPExcelReader->setActiveSheetIndexByName($this->Sheet);
        }
        $ActiveSheet = $objPHPExcelReader->getActiveSheet();
        $end_time1 = date('Y-m-d H:i:s');
        $process_time1 = strtotime($end_time1) - strtotime($start_time1);
        Log::info('getActiveSheet function call time : ' . $process_time1 . ' Seconds');

        $start_time1 = date('Y-m-d H:i:s');
        $drow = $ActiveSheet->getHighestDataRow();
        $end_time1 = date('Y-m-d H:i:s');
        $process_time1 = strtotime($end_time1) - strtotime($start_time1);
        Log::info('getHighestDataRow function call time : ' . $process_time1 . ' Seconds');

        $start_time1 = date('Y-m-d H:i:s');
        $dcol = $ActiveSheet->getHighestDataColumn();
        $end_time1 = date('Y-m-d H:i:s');
        $process_time1 = strtotime($end_time1) - strtotime($start_time1);
        Log::info('getHighestDataColumn function call time : ' . $process_time1 . ' Seconds');

        $start_row = intval(self::$start_row) + 1;
        $end_row   = $limit > 0 ? ($start_row + $limit) : ($drow - self::$end_row);
        //$end_row   = ($drow - intval(self::$end_row));

        $start_time1 = date('Y-m-d H:i:s');
        $all_rows = $ActiveSheet->rangeToArray('A' . $start_row . ':' . $dcol . $end_row);
        $end_time1 = date('Y-m-d H:i:s');
        $process_time1 = strtotime($end_time1) - strtotime($start_time1);
        Log::info('rangeToArray function call time : ' . $process_time1 . ' Seconds');
        Log::info('start row : ' . $start_row);

        //Log::info(print_r($all_rows,true));
        $end_time = date('Y-m-d H:i:s');
        $process_time = strtotime($end_time) - strtotime($start_time);
        Log::info('Convert to csv read time : ' . $process_time . ' Seconds');

        if($this->first_row == self::$COLUMN_NAMES) {
            $start_time = date('Y-m-d H:i:s');

            $result = $first_row = array();

            $i = 0;
            foreach ($all_rows as $row) {
                if ($i == 0) {
                    $first_row = $row;
                } else {
                    $j = 0;
                    foreach ($row as $column) {
                        $result[$i - 1][$first_row[$j]] = $column;
                        $j++;
                    }
                }
                $i++;
            }

            $end_time = date('Y-m-d H:i:s');
            $process_time = strtotime($end_time) - strtotime($start_time);
            Log::info('loop time : ' . $process_time . ' Seconds');

        } else {
            $result = $all_rows;
        }
        //Log::info(print_r($result, true));

        return $result;
    }

    public static function getSheetNamesFromExcel($filepath) {
        $objPHPExcelReader = PHPExcel_IOFactory::load($filepath);

        return $objPHPExcelReader->getSheetNames();
    }
}