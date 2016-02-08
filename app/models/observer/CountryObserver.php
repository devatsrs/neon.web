<?php

/**
 * Class UserObserver
 */
class CountryObserver {

	public function saving(Eloquent $model) {

		try{

			$model::clearCache();

		}catch(Exception $ex){


		}

	}

}