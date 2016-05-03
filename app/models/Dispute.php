<?php

class Dispute extends \Eloquent {
	protected $fillable = [];
	protected $connection = 'sqlsrv2';

	protected $guarded = array("DisputeID");
	protected $table = 'tblDispute';
	protected  $primaryKey = "DisputeID";
	const  PENDING = 0;
	const SETTELED =1;
	const CANCEL  = 2;

	public static $Type = [0=>'Select Status',self::PENDING=>'Pending',self::SETTELED=>'Setteled',self::CANCEL=>'Cancel'];

	public static function calculate_dispute($GrandTotal,$DisputeTotal,$TotalMinutes,$DisputeMinutes){

		$DisputeTotal = number_format($DisputeTotal,4);
		$DisputeDifference = number_format($GrandTotal - $DisputeTotal,4);
		$total_average = $GrandTotal + $DisputeTotal / 2;

		$DisputeDifferencePer =  number_format(abs(($DisputeDifference / $total_average) * 100),4);

		$DisputeMinutes = $DisputeMinutes;
		$MinutesDifference = $TotalMinutes - $DisputeMinutes;
		$minutes_average = $TotalMinutes + $DisputeMinutes / 2;
		$MinutesDifferencePer =  number_format(abs(($MinutesDifference / $minutes_average) * 100),4);

		return array(

			"DisputeTotal" => $DisputeTotal,
			"DisputeDifference" => $DisputeDifference,
			"DisputeDifferencePer" => $DisputeDifferencePer,

			"DisputeMinutes" => $DisputeMinutes,
			"MinutesDifference" => $MinutesDifference,
			"MinutesDifferencePer" => $MinutesDifferencePer,
		);

	}
}