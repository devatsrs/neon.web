<?php

class LCR extends \Eloquent {

	protected $fillable = [];
	protected $table = "";

	const LCR_PREFIX = 1;
	const LCR = 2;

	public static $policy = array( self::LCR_PREFIX => "LCR + PREFIX", self::LCR => "LCR" );
	public static $position = array( "5" => "5", "10" => "10" );


}