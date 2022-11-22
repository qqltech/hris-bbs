<?php

namespace App\Traits;
use  App\Helpers\Cryptor;
use App\Casts\Upload;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use DateTimeInterface;
use Carbon\Carbon;

trait ModelTrait {
    /**
     * Additional function
     */
    public $fileColumns    = [];
    public $autoValidator  = true;
    public $useEncryption  = false;

    public $importValidator = [];   // laravel validation

    public $excepts         = [];
    public $createAdditionalData = []; // example: 'field'=>'auth:id'
    public $updateAdditionalData = [];
    public $truncatable = true;
    public $generateRoute = true;

    /**
     * Menyesuaikan sesuai timezone saat casts date
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Untuk keperluan authenticate frontend
     */
    public function custom_authenticate( $req ) 
    {
        return true;
    }

    /**
     *  @param object [ 'type'=>'find' or 'get', 'caller'=> null ]
     */
    public function scopeFinal( $query, object $option )
    {
        return $query;
    }

    /**
     *  @param object
     */
    public function scopeSearch( $query, $allColumns )
    {
        $string  = trim(strtolower(req('search')));
        $string = str_replace( [ '\\','(',')', "'" ],[ "\'", '\\\\','\(','\)' ], $string);
        $table = $this->getTable();
        $searchfield = req('searchfield');
        $additionalString = getDriver()=="pgsql"?"::text":"";

        $isAutoPrefix = req('auto_prefix')===null?true:req('auto_prefix');
        $isAutoPrefix = $isAutoPrefix==='false'?false:true;
        $aliases = [];
        if( method_exists( $this, 'aliases') ){
            $aliases = $this->aliases();
        }
        $query->where(
            function ($query)use($allColumns,$string,$additionalString, $searchfield,$table,$isAutoPrefix,$aliases) {
                if($searchfield!=null){
                    $searchfieldArray = explode(",", strtolower($searchfield) );
                    foreach($searchfieldArray as $fieldSearching){
                        if($isAutoPrefix && strpos($fieldSearching,".")===false){
                            $fieldSearching = "this.$fieldSearching";
                        }
                        $fieldSearching = str_replace( "this.","$table.", $fieldSearching );
                        $query->orWhereRaw( "LOWER($fieldSearching$additionalString) LIKE '%$string%'");
                    }
                }else{
                    foreach($allColumns as $column){
                        if((strpos($column, '.id') !== false)||(strpos($column, '_id') !== false) ){
                            continue;
                        }
                        $kolomFixed = $column.$additionalString;
                        if(strpos( strtolower($kolomFixed), ' as ' )!==false){
                            $kolomFixedArr = explode(' as ', strtolower($kolomFixed));
                            $kolomFixed = end($kolomFixedArr);
                        }
                        $keyAliased = array_search( $column,$aliases ) ;
                        if( $keyAliased ){
                            $kolomFixed = $keyAliased;
                        }
                        $query->orWhereRaw("LOWER($kolomFixed) LIKE '%$string%'");
                    }
                }
        });
    }

    /**
     *  @param object
     */
    public function scopeOrin( $query )
    {
        if( trim(explode(":", req("orin"))[1]) == '' ) return;
        $table = $this->getTable();
        $columnIn = explode(":", req("orin"))[0];
        $idsIn = explode(",", explode(":", req("orin"))[1]);
        $query->orWhereRaw( str_replace("this.","$table.", $columnIn)." IN (".implode(',',$idsIn).")" );
    }

    /**
     *  @param object
     */
    public function scopeFilters( $query )
    {
        if( !app()->request->isMethod('GET') ) return;
        $filteredCols = (array)req2('filter_%');
        if(!$filteredCols)  return;
        $aliases = [];
        if( method_exists( $this, 'aliases') ){
            $aliases = $this->aliases();
        }
        $model = $this;
        $additionalString = getDriver()=="pgsql"?"::text":"";
        $operator = $filteredCols['filter_operator'] ?? (getDriver()=="pgsql"?"~*":'LIKE');
        $query->where(function($q) use($filteredCols, $model, $additionalString, $operator, $aliases){
            $table = $model->getTable();
            foreach( $filteredCols as $key => $val){
                if( $key == 'filter_operator' ) continue;

                $column = str_replace("filter_", "", $key);
                $keyAliased = array_search( $column,$aliases ) ;
                if( $keyAliased ){
                    $column = $keyAliased;
                }

                if( !Str::contains($column, '.') ){
                    $dataType = getDataType($model, $column);
                    $column = in_array($column, $model->columns)?"this.$column":$column;
                }else{
                    $colArr = explode(".", $column);
                    if($model=getBasic( $colArr[0])){
                        $dataType = getDataType( $model, $colArr[1] );
                    }else{
                        $dataType = 'text';
                    }
                }

                $dataType = Str::lower($dataType);

                if( in_array($dataType, ['date']) ){
                    $column = getDriver()=='mysql'?"DATE_FORMAT($column, '%d/%m/%Y')":"to_char($column, 'DD/MM/YYYY')";
                }elseif( in_array($dataType, ['datetime','timestamp']) ){
                    $column = getDriver()=='mysql'?"DATE_FORMAT($column, '%d/%m/%Y %H:%i')":"to_char($column, 'DD/MM/YYYY HH24:MI')";
                }

                $column = str_replace("this.", "$table.", $column);
                $val = trim($val);
                if( strtolower($operator) == 'like' ){
                    $val = "%$val%";
                }
                $val = trim(strtolower($val));
                $val = str_replace( [ '\\','(',')', "'" ],[ "\'",'\\\\','\(','\)' ], $val);
                $q->whereRaw( "LOWER($column$additionalString) $operator (?)", [strtolower($val)] );
            }
        });
    }

        /**
     *  @param object
     */
    public function scopeDirectFilters( $query )
    {
        if( !app()->request->isMethod('GET') ) return;
        $operators = ["is not ", "=", "<> ", "!= ", "> ", ">= ", "< ", "is ", "<=", "not in ", "in ", "like ", "ilike ", "~*", 'between '];
        $filteredCols = (array)req2('if_%');
        if(!$filteredCols)  return;
        $table = $this->getTable();
        $query->where(function($q) use($filteredCols, $table, $operators){
            foreach( $filteredCols as $key => $val){
                $key = str_replace("this.", "$table.", $key);
                $fixedOperator = '=';
                $valLower = strtolower($val);

                foreach($operators as $operator){
                    if(Str::startsWith($valLower, $operator )){
                        $fixedOperator = trim(explode(' ', $valLower, 2 )[0]);
                        $val = Str::replaceFirst($fixedOperator,'', $val);// str_ireplace($fixedOperator,'', $val);
                        $val = trim($val);
                        break;
                    }
                }

                $column = Str::replaceFirst("if_", "", $key);//str_replace("if_", "", $key);
                $val = strtolower($val);
                $val = str_replace( [ '\\','(',')', "'" ],[ "\'",'\\\\','\(','\)' ], $val);
                if(in_array( Str::lower($fixedOperator), ['between','in', 'not in']) ){
                    $valArr = Str::contains($val, ',') ? explode(",", $val) : explode("~", $val);
                    if( Str::lower($fixedOperator)=='between' && Str::contains($valArr[0], '/') ){
                        $valArr = array_map(function($dt){
                            return Carbon::createFromFormat(env("FORMAT_DATE_FRONTEND","d/m/Y"), $dt)->format('Y-m-d');
                        }, $valArr);
                    }
                    $fixedOperator = str_replace(' ', '', "where$fixedOperator");
                    $q->$fixedOperator( $column, $valArr );
                }else{
                    $q->where( $column, $fixedOperator, $val );
                }
            }
        });
    }

    /**
     *  @param object
     */
    public function scopeOrNull( $query )
    {
        $table = $this->getTable();
        $columnsArr = explode(',', str_replace("this.", "$table.", req('orWhereNull')));
        $query->orWhere(function($q)use($columnsArr){
            foreach( $columnsArr as $col ){
                $q->orWhereNull($col);
            }
        });
    }

    /**
     *  @param object
     */
    public function scopeNull( $query )
    {
        $table = $this->getTable();
        $columnsArr = explode(',', str_replace("this.", "$table.", req('whereNull')));
        $query->where(function($q)use($columnsArr){
            foreach( $columnsArr as $col ){
                $q->whereNull($col);
            }
        });
    }

    /**
     *  @param object
     */
    public function scopeOrNotNull( $query )
    {
        $table = $this->getTable();
        $columnsArr = explode(',', str_replace("this.", "$table.", req('orWhereNotNull')));
        $query->orWhere(function($q)use($columnsArr){
            foreach( $columnsArr as $col ){
                $q->orWhereNotNull($col);
            }
        });
    }

    /**
     *  @param object
     */
    public function scopeNotNull( $query )
    {
        $table = $this->getTable();
        $columnsArr = explode(',', str_replace("this.", "$table.", req('whereNotNull')));
        $query->where(function($q)use($columnsArr){
            foreach( $columnsArr as $col ){
                $q->whereNotNull($col);
            }
        });
    }

    /**
     *  @param object
     */
    public function scopeNotin( $query )
    {
        if( trim(explode(":", req("notin"))[1]) == '' ) return;
        $table = $this->getTable();
        $columnNotIn = explode(":", req("notin"))[0];
        $idNotIn = explode(",", explode(":", req("notin"))[1]);
        $query->whereNotIn(str_replace("this.","$table.", $columnNotIn), $idNotIn );
    }

    /**
     *  @param object
     */
    public function scopeQueryParam( $query )
    {
        $table = $this->getTable();
        $queryNames = req("query_name");

        $query->where(function($q)use($table, $queryNames){
            foreach(explode( ',', $queryNames ) as $queryName){
                if( !$queryName ) continue;
                $rawWhere = DB::table("default_params")
                    ->select("prepared_query","params")
                    ->where("name",$queryName)->first();
                
                if(!$rawWhere){
                    trigger_error(json_encode(["errors"=>"query_name ".req('query_name')." does not exist"]));
                }

                $whereStr = $rawWhere->prepared_query;
                if( !empty($rawWhere->params) ){
                    $paramsArr = explode(",", $rawWhere->params);
                    $backendParams = [];
                    $frontendParams = [];
                    $frontendParamSent = (array) req();
                    foreach($paramsArr as $param){
                        if( strpos($param,"backend_")===false ){
                            if(!in_array($param, array_keys($frontendParamSent)) ) {
                                $frontendParamSent[$param] = \PDO::PARAM_NULL;
                                abort(422, json_encode([
                                    "errors"=>"parameter $param does not exist",
                                    "message"=>"parameter $param does not exist"
                                ]));
                            }
                            $frontendParams[] = $param;
                        }else{
                            $backendParams[] = $param;
                        }
                    }

                    $acceptedParams = Arr::only( $frontendParamSent, $frontendParams );
                    if( config( $queryName ) ){
                        $acceptedParams = array_merge( $acceptedParams, config( $queryName ) );
                    }
                    
                    $q->whereRaw(str_replace("this.","$table.", $whereStr ), $acceptedParams );
                }else{
                    $q->whereRaw(str_replace("this.","$table.",$whereStr ) );
                }
            }
        });
    }
    /**
     * for frontend usage, checking auth before request data
     */
    public function custom_authorize( $req ){
        return response()->json( [
            'success'=>true
        ],200);
    }

    public function getEncrypter(){
        // $keyPlain = env('APP_KEY');// another example to generate string: md5('12345678910');
        // $key = substr($keyPlain, 0, 32);
        // $encrypter = new \Illuminate\Encryption\Encrypter( $key, config('app.cipher' ) /**AES-256-CBC*/ );
        $encrypter = new Cryptor /**AES-256-CBC*/;
        return $encrypter;
    }

    public function encrypt( $plainText ){
        $encrypter = $this->getEncrypter();
        return $encrypter->encrypt( $plainText );
    }

    public function  decrypt( $encryptedText ){
        $encrypter = $this->getEncrypter();
        return $encrypter->decrypt( $encryptedText, true );
    }

    public function createValidator(){
        return [];
    }

    public function updateValidator(){
        return [];
    }

    public function customFind($p){
        if( method_exists( $this, "queryCacheById") ){
            $self = $this;
            $cacheOpt = $this->queryCacheById( $p );
            $cacheKey = $cacheOpt[ 'key' ].$p->id;
            $cacheTime = $cacheOpt[ 'time' ];

            return Cache::remember( $cacheKey, $cacheTime, function()use( $self, $p ){
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
            return Cache::remember( $cacheKey, $cacheTime, function()use( $self, $p ){
                return _customGetData($self, $p);
            } );
        }
        
        return _customGetData($this, $p);
    }

    /**
     * @override Laravel function
     */
    public function getCasts(){
        $custom = getCustom( getTableOnly( $this->getTable() ) );
        if(!$custom) return [];
        if(!$custom->useEncryption){
            $custom->casts[$custom->getKeyName()] = 'integer';
        }
        if( count($custom->fileColumns)>0 ){
            foreach($custom->fileColumns as $col){
                $custom->casts[$col] = Upload::class;
            }
        }
        foreach( $this->columnsFull as $col ){
            $colArr = explode(":", $col);
            $columnName = $colArr[0];
            $dataType = $colArr[1];

            if( app()->request->isMethod('GET') && !in_array( $columnName, array_keys($custom->casts) )){
                if( Str::contains($dataType,'boolean') ){
                    $custom->casts[ $columnName ] = 'boolean';
                }elseif( Str::contains( $dataType, 'int' ) ){
                    $custom->casts[ $columnName ] = 'integer';
                }elseif( Str::contains( $dataType, 'decimal' ) || Str::contains( $dataType, 'numeric' ) ){
                    $custom->casts[ $columnName ] = 'float';
                }elseif( Str::contains( $dataType, 'json' ) ){
                    $custom->casts[ $columnName ] = 'array';
                }
            }
        }
        $casts = $custom->casts;
        return array_merge($casts, getCastsParam());
    }

    public function getIdAttribute($val){
        if(!$this->useEncryption){
            return (int)$val;
        }
        return $this->encrypt( $val );
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

    public function custom_upload( $request ){
        $validator = Validator::make($request->all(), [
            'file' => "required|file|max:10000|mimetypes:image/bmp,text/csv,image/gif,image/vnd.microsoft.icon,image/jpeg,image/png,image/svg+xml,image/tiff,text/html,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.ms-powerpoint,application/vnd.rar,application/zip,text/plain,image/webp,application/pdf,audio/wav,audio/webm,audio/mpeg,video/x-msvideo,video/mp4,video/mpeg,video/webm,application/octet-stream",
            'field'=> "required|string|".(count($this->fileColumns)>0?"in:".implode(",",$this->fileColumns):"")
        ]);
        if ( $validator->fails()) {
            return abort(422, json_encode([ 
                'message'   => "Berkas tidak diterima, pastikan jenis dan ukuran tepat.",
                "errors"    => $validator->errors()
            ]));
        }
        
        $field = $request->field;
        $modelName = getTableOnly( $this->getTable() );
        $file = $request->file;
        $userId = ($request->user()->id ?? 'anonymous');
        
        $resultKey  = saveFileToCache( $modelName, $field, $file, $userId );
        return response()->json(['message'=>'Successfully stored temporary file and will be removed in 30 mins', 'key'=> $resultKey]);
    }

    public function custom_generate_row( $request ){
        return createModelRow($this);
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

    function extendJoin( $model ) use($self)
    {
        $runtimeSql = "SELECT * FROM other_tables";
        $model = $self->leftJoinSub( $runtimeSql, 'runtime_sql', function ($join) {
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

    protected static function booted()
    {
        parent::boot();
        static::creating(function ( $model ) {
            $custom = getCustom( getTableOnly( $model->getTable() ) );
            if( method_exists($custom, 'onCreating') ) $custom->onCreating($model);
        });

        self::created(function( $model ){
            $custom = getCustom( getTableOnly( $model->getTable() ) );
            if( method_exists($custom, 'onCreated') ) $custom->onCreated($model);
        });

        self::saving(function( $model ){
            $custom = getCustom( getTableOnly( $model->getTable() ) );
            if( method_exists($custom, 'onSaving') ) $custom->onSaving($model);
        });

        self::saved(function( $model ){
            $custom = getCustom( getTableOnly( $model->getTable() ) );
            if( method_exists($custom, 'onSaved') ) $custom->onSaved($model);
        });

        self::updating(function( $model ){
            $custom = getCustom( getTableOnly( $model->getTable() ) );
            if( method_exists($custom, 'onUpdating') ) $custom->onUpdating($model);
        });

        self::updated(function( $model ){
            $custom = getCustom( getTableOnly( $model->getTable() ) );
            if( method_exists($custom, 'onUpdated') ) $custom->onUpdated($model);
        });

        self::deleting(function( $model ){
            $custom = getCustom( getTableOnly( $model->getTable() ) );
            if( method_exists($custom, 'onDeleting') ) $custom->onDeleting($model);
        });

        self::deleted(function( $model ){
            $custom = getCustom( getTableOnly( $model->getTable() ) );
            if( method_exists($custom, 'onDeleted') ) $custom->onDeleted($model);
        });

        self::retrieved(function( $model){
            $custom = getCustom( getTableOnly( $model->getTable() ) );
            if( method_exists($custom, 'onRetrieved') ) $custom->onRetrieved($model);
            if($custom->useEncryption){
                foreach ($model->toArray() as $key => $val) {
                    if (Str::endsWith($key, '_id') ) {
                        $model[$key] = $model->encrypt( $val );
                    } else if (strpos($key, '.') > -1) {
                        $exp = explode('.', $key);
                        if (Str::endsWith($exp[count($exp) - 1], '_id') && is_numeric($val)) {
                            $model[$key] = $model->encrypt($val);
                        } else if (Str::is('id', $exp[count($exp) - 1]) && is_numeric($val)) {
                            $model[$key] = $model->encrypt($val);
                        }
                    }
                }
            }
        });        
    }

    protected $allowedParams = [
        'order_by','order_type', 'order_by_raw',
        'search', 'searchfield', 'paginate',
        'page', 'join','group_by', 'simplest', 'single',
        'notin', 'query_name', 'api_version', 'transform',
        'filter_operator'
    ];

    public $allowedDangerousParams = [
        'where', 'selectfield', // 'addselect', 'addjoin'
    ];
    
    public function isParamAllowed( string $param )
    {
        $finalParams = array_merge( $this->allowedParams, $this->allowedDangerousParams );
        return in_array( Str::lower($param), $finalParams );
    }
}