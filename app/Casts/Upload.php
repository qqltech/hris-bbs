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
        if( !$value || ($value && !\Str::contains($value,env("FILE_SEPARATOR", ":::")) )){
            return $value;
        }
        if(app()->request->isMethod('GET')){
            $prefix = '';
            $subDomain = strtolower(explode('.', @$_SERVER['HTTP_HOST']??'.')[0]);
            if(\File::exists( base_path(".env.$subDomain") ) ){
                $prefix = "$subDomain/";
            }
            $fixedPath = "/uploads/$prefix".getTableOnly( $model->getTable() )."/$value";
            if( !\File::exists( public_path($fixedPath) ) ){
                return url("/uploads/".getTableOnly( $model->getTable() )."/$value");
            }

            return url("/uploads/$prefix".getTableOnly( $model->getTable() )."/$value");
        }
        $dataArr = explode("/", $value);
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
                if(\Str::contains($value, env("FILE_SEPARATOR", ":::"))){
                    $valueArr = explode("/", $value);
                    $value = end($valueArr);
                    return $value;
                }
                $oldFileArr = explode(env("FILE_SEPARATOR", ":::"), $model->$key);
                $oldFile = end($oldFileArr);
                $oldFile = $model->$key;
            }
            $value = moveFileFromCache( $modelName, $field, $file, $userId, $oldFile );
        }
        return $value;
    }
}