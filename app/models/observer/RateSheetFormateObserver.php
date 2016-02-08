<?php

/**
 * Class UserObserver
 */
class RateSheetFormateObserver {

	public function saving(Eloquent $model) {

		try{

			$model::clearCache();

		}catch(Exception $ex){


		}

	}

}