<?php

/**
 * Class UserObserver
 */
class TrunkObserver   {

	public function saving(Eloquent $model) {

		try{

			$model::clearCache();
			//Debugbar::info("clearCache called... ");

		}catch(Exception $ex){


		}

	}

}