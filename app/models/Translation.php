<?php

class Translation extends \Eloquent {
	
	protected $guarded = array('TranslationID');

    protected $table = 'tblTranslation';

    protected  $primaryKey = "TranslationID";

	public static $enable_cache = false;

    public static $cache = array(
    "language_dropdown1_cache",   // Country => Country
    "language_cache",    // all records in obj
);

    public static function getLanguageDropdownList(){

        if (self::$enable_cache && Cache::has('language_dropdown1_cache')) {
            //check if the cache has already the ```user_defaults``` item
            $admin_defaults = Cache::get('language_dropdown1_cache');
            //get the admin defaults
            self::$cache['language_dropdown1_cache'] = $admin_defaults['language_dropdown1_cache'];
        } else {
            //if the cache doesn't have it yet
            self::$cache['language_dropdown1_cache'] = Translation::join('tblLanguage', 'tblLanguage.LanguageID', '=', 'tblTranslation.LanguageID')
                                                                    ->select("tblLanguage.ISOCode", "tblTranslation.Language")->get();

            //cache the database results so we won't need to fetch them again for 10 minutes at least
            Cache::forever('language_dropdown1_cache', array('language_dropdown1_cache' => self::$cache['language_dropdown1_cache']));

        }

        return self::$cache['language_dropdown1_cache'];
    }
}