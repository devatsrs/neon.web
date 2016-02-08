<?php
/** not used
 * Class BaseObserver
 */
class BaseObserver {

    protected $validator;

    protected $rules = [];

    /*public function __construct(Illuminate\Validation\Factory $validator){
        //$this->validator = $validator->make([], $this->rules);
    }*/

    public function saving(Eloquent $model){
        /*$this->validator->setData($model->getAttributes());

        $this->setConditionalRules($model);

        if($this->validator->fails()){
            $model->setAttribute('errors', $this->validator->errors());
            return false;
        }*/
    }

    public function saved(Eloquent $model){ $this->flushCache($model); }

    public function updating(Eloquent $model){ $this->flushCache($model); }

    public function updated(Eloquent $model){ $this->flushCache($model);}

    public function creating(Eloquent $model){ $this->flushCache($model);}

    public function created(Eloquent $model){ $this->flushCache($model);}

    public function deleting(Eloquent $model){ $this->flushCache($model);}

    public function deleted(Eloquent $model){ $this->flushCache($model);}

    public function restoring(Eloquent $model){ $this->flushCache($model);}

    public function restored(Eloquent $model){ $this->flushCache($model);}

    //protected function setConditionalRules(Eloquent $model){ $this->flushCache($model); }

    public function flushCache(Eloquent $model){

        try{


       // echo $model->getModel();   

            //print_r($model);


           /* foreach($model::cache as $key){

                if(Cache::has($key)){
                    Cache::tag($key)->flush();  
                    Debugbar::info($key ."Flushing... ");      
                }


            }*/

        }catch(Exception $ex){

            // Debugbar::info("Flushing... " . $ex->getMessage());   

        }
        
        

    }

}
