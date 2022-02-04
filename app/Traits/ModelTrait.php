<?php

namespace App\Traits;

trait ModelTrait {
    /**
     * Additional function
     */
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
}