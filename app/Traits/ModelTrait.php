<?php

namespace App\Traits;

trait ModelTrait {
    /**
     * Additional function
     */
    public $autoValidator  = true;

    public function createValidator(){
        return [];
    }

    public function updateValidator(){
        return [];
    }

    public $importValidator = [];   // laravel validation

    public $excepts         = [];
    public $createAdditionalData = []; // example: 'field'=>'auth:id'
    public $updateAdditionalData = [];

    public function customFind($p){
        if( method_exists( $this, "queryCacheById") ){
            $self = $this;
            $cacheOpt = $this->queryCacheById( $p );
            $cacheKey = $cacheOpt[ 'key' ].$p->id;
            $cacheTime = $cacheOpt[ 'time' ];

            return \Cache::remember( $cacheKey, $cacheTime, function()use( $self, $p ){
                return _customFind($self, $p);
            } );
        }
        
        return _customFind($this, $p);
    }

    public function customGet($p){
        if( method_exists( $this, "queryCache") ){
            $self = $this;
            $cacheOpt = $this->queryCache( $p );
            $cacheKey = $cacheOpt[ 'key' ];
            $cacheTime = $cacheOpt[ 'time' ];
            return \Cache::remember( $cacheKey, $cacheTime, function()use( $self, $p ){
                return _customGetData($self, $p);
            } );
        }
        
        return _customGetData($this, $p);
    }

    /**
     * @override Laravel function
     */
    public function getCasts(){
        return array_merge($this->casts, getCastsParam());
    }

    function overrideGetParams( object $params, int $id=null )
    {
        if( $id ){
            $params->selectfield = $params->selectfield;
            $params->join        = $params->join;
            $params->single      = $params->single;
            $params->addSelect   = null;
            $params->addJoin     = null;
            $params->joinMax 	 = $params->joinMax;
        }else{
            $params->where_raw   = $params->where_raw;
            $params->order_by    = $params->order_by;
            $params->order_type  = $params->order_type;
            $params->order_by_raw= $params->order_by_raw;
            $params->search      = $params->search;
            $params->searchfield = $params->searchfield;
            $params->selectfield = $params->selectfield;
            $params->paginate    = $params->paginate;
            $params->join        = $params->join;
            $params->addSelect   = null;
            $params->addJoin     = null;
            $params->notIn       = @$params->notIn;
            $params->joinMax 	 = $params->joinMax;
            $params->group_by    = $params->group_by;
        }
        return $params;
    }

    function createRoleCheck()
    {
        return true;
    }

    function updateRoleCheck( $id )
    {
        return true;
    }

    function deleteRoleCheck( $id )
    {
        return true;
    }

    function readRoleCheck( $id )
    {
        return true;
    }

    function listRoleCheck()
    {
        return true;
    }

    function createBefore( $model, $arrayData, $metaData, $id=null )
    {
        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    function createAfter( $model, $arrayData, $metaData, $id=null )
    {        
        //  code here
    }

    function updateBefore( $model, $arrayData, $metaData, $id=null )
    {
        $newArrayData  = array_merge( $arrayData,[] );
        return [
            "model"  => $model,
            "data"   => $newArrayData,
        ];
    }

    function updateAfter( $model, $arrayData, $metaData, $id=null )
    {
        //  code here
    }

    function deleteBefore( $model, $arrayData, $metaData, $id=null )
    {
        return [
            "model" => $model
        ];
    }

    function deleteAfter( $model, $arrayData, $metaData, $id=null )
    {
        
    }

    /*
    function createAfterTransaction( $newdata, $olddata, $data, $meta )
    {
        
    }

    function updateAfterTransaction( $newdata, $olddata, $data, $meta )
    {

    }
    
    function transformRowData( array $row )
    {
        return array_merge( $row, [] );
    }

    function transformArrayData( array $arrayData  )
    {
        foreach( $arrayData as $idx => $singleData ){
            $arrayData[ $idx ]['newColumn'] = 'nice day!';
        }
        return $arrayData;
    }

    function extendJoin( $model )
    {
        $runtimeSql = "SELECT * FROM other_tables";
        $model = $model->leftJoinSub( $runtimeSql, 'runtime_sql', function ($join) {
            $join->on("{$this->getTable()}.id", '=', 'runtime_sql.id');
        });
        
        return $model;
    }

    function queryCacheById( object $param )
    {
        return [
            'key'   => 'new-key',
            'time'  => 60*60
        ];
    }

    function queryCache( object $param )
    {
        return [
            'key'   => 'new-key',
            'time'  => 60*60
        ];
    }

    function custom_exportexcel( $request )
    {
        $query = \DB::table($this->getTable())->get();
        if( count($query)==0 ){
            return "data kosong";
        }else{
            return \Excel::download(new \ExportExcel($query), \Carbon::now()->format('d-m-Y')."_".$this->getTable().'.xlsx');    
        }
    }

    function custom_importexcel($request)
    {
        if(!$request->hasFile("file")){
            return response()->json("file harus ada",400);
        }
        return _uploadexcel($this, $request);
    }
*/
}