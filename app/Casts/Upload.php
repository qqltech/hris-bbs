<?php

namespace App\Casts;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
 
class Upload implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     */
    public function get($model, $key, $value, $attributes)
    {
        if( !$value || ($value && \Str::contains(":::", $value) )){
            return $value;
        }
        $dataArr = explode(":::", $value);
        return end($dataArr);
    }
 
    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  array  $value
     * @param  array  $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {

        $custom = getCustom( getTableOnly( $model->getTable() ) );
        if( count($custom->fileColumns)>0 && in_array($key,$custom->fileColumns) ){
            $modelName = getTableOnly( $model->getTable() );
            $field = $key;
            $file = $value;
            $userId = \Auth::user()->id;
            $oldFile = null;
            
            if( $model->$key ){
                $oldFileArr = explode(":::", $model->$key);
                $oldFile = end($oldFileArr);
                if($oldFile == $value){
                    $oldFile = $model->$key;
                }else{
                    $oldFile = null;
                }
            }
            $value = moveFileFromCache( $modelName, $field, $file, $userId, $oldFile );
        }
        return $value;
    }
}