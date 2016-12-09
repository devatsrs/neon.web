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
    public static $DATA    			= 	1;
    public static $EXCEL   			= 	'xlsx'; // Excel file
    public static $EXCELs  			= 	'xls'; // Excel file
    public static $CSV 	   			= 	'csv'; // csv file


    public function __construct($file , $csvoption = array())
    {
        $this->file = $file;
        $this->sheet = 0;
        $this->first_row = self::$COLUMN_NAMES;
        $this->file_type = self::$CSV;

        $this->set_file_type();
        $this->get_file_settings($csvoption);

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

            return $this->read_excel($this->file,$limit);
        }
		
		if($this->file_type == self::$EXCELs){

            return $this->read_xls_excel($this->file,$limit);
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

        $result = array();
        $this->reader = ReaderFactory::create(Type::CSV); // for XLSX files
        $this->set_file_settings();
        $this->reader->open($filepath);

        foreach($this->reader->getSheetIterator() as $key  => $sheet) {

            // For First Sheet only.
            if($key == 1) {

                foreach ($sheet->getRowIterator() as $row) {

                    if($limit > 0 && $limit == $this->row_cnt) {
                        break;
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

                    $result[] = $this->set_row($row);

                    $this->row_cnt++;

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


        $this->reader = ReaderFactory::create(Type::XLSX); // for XLSX files
        $this->reader->open($filepath);
        $result = array();

        foreach($this->reader->getSheetIterator() as $key  => $sheet) {

            // For First Sheet only.
            if($key == 1) {

                foreach ($sheet->getRowIterator() as $row) {

                    if($limit > 0 && $limit == $this->row_cnt) {
                        break;
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

                    $result[] = $this->set_row($row);
                    $this->row_cnt++;

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
	 public function read_xls_excel($filepath,$limit=0){
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
			$results = Excel::selectSheetsByIndex(0)->load($filepath, function ($reader) use ($flag,$isExcel) {
				if ($flag == 1) {
					$reader->noHeading();
				}
			})->take($limit)->toArray();
			
			return $results;
				 
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
            if( method_exists($row_value , "format") ) {

                $col_row[$col_key] = $row_value->format("H:i:s")!='00:00:00'?$row_value->format("Y-m-d H:i:s"):$row_value->format("Y-m-d");

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

}