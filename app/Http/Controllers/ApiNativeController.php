<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class ApiNativeController extends Controller
{
    public function index( Request $r, $name, $id = null ){
        $q = DB::table("default_params")
            ->select("prepared_query", "params","updated_at")
            ->where("name", $name)->first();

        if(!$q) return response()->json([
            "message"=>"Maaf resource tidak tersedia"
        ], 404);

        $query = $q->prepared_query;
        $params = $q->params;

        $builder = new \Staudenmeir\LaravelCte\Query\Builder(\DB::connection());

        $builder->from($name)
            ->withExpression( $name, $query );

        $clientBindings = $r->all();
        $mustBindings = explode(',', $params);
        $fixBindings = [];

        foreach($mustBindings AS $bindKey){
            if(!$bindKey) continue;
            if( in_array($bindKey, array_keys($clientBindings)) ){
                $fixBindings[$bindKey] = $clientBindings[$bindKey];
            }else{
                $fixBindings[$bindKey] = NULL;
            }
        }
        // return $fixBindings;
        $builder->setBindings($fixBindings, 'expressions');
        
        if( $id ) {
            return response()->json([
                "data" => $builder->whereRaw( "id=:id", ['id'=>$id] )->first()
            ]);
        }

        if( $r->has('orderby') ){
            $builder->orderBy($r->orderby,  $r->has('ordertype')? $r->ordertype:'ASC');
        }

        if( $r->has('search') ){
            $searchText = $r->search;
            $casterString = getDriver()=="pgsql"?"::text":"";
            $cols = \Cache::get("schema_native_$name:$q->updated_at")??[];
            $builder->where(function($q)use($cols, $casterString, $searchText){
                foreach($cols as $col){
                    if( !in_array($col, ['id']) ){
                        $q->orWhereRaw(DB::raw("LOWER($col$casterString) LIKE '%$searchText%'"));
                    }
                }
            });
        }

        
        $data = $builder->paginate($r->has('paginate') ? $r->paginate : 25);
        $cols = \Cache::get("schema_native_$name:$q->updated_at");
        if( !$cols ){
            $rows = (array)$data->toArray()['data'];
            if( count($rows)>0 ){
                $row = (array)$rows[0];
                \Cache::put("schema_native_$name:$q->updated_at", array_keys($row), 60*60*30);
            }
        }
        return $data;

        return $builder->paginate($r->has('paginate') ? $r->paginate : 25);
    }
}