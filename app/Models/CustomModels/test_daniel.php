<?php

namespace App\Models\CustomModels;

class test_daniel extends \App\Models\BasicModels\test_daniel
{    
    public function __construct()
    {
        parent::__construct();
    }
    
    public $fileColumns    = [ /*file_column*/ ];

    //public $createAdditionalData = ["creator_id"=>"auth:id"];
    //public $updateAdditionalData = ["last_editor_id"=>"auth:id"];

    public function custom_createBarang(){
        $request = app()->request;
        // return $request;
         $id = test_daniel::create([
            'nama_barang' => $request->nama_barang,
            "img_url" => $request->img_url,
            'qty' => $request->qty,
            'status' => $request->status,
        ]);
        test_daniel_2::create([
            'id_barang' => $id['id'],
            'harga' => $request->harga,
            'status' => $request->status,
        ]);
        $result = [
            'message' => 'success',
            'error' => 'false',
        ];
        return response($result, 201);
    }

    public function custom_updateBarang(){
        $request = app()->request;
        $id = request('id');
        //return $id;
        test_daniel::where('id',$id)->update([
            'nama_barang' => $request->nama_barang,
            'img_url' => $request->img_url,
            'qty' => $request->qty,
            'status' => $request->status,
        ]);
        test_daniel_2::where('id_barang',$id)->update([
            'id_barang' => $id,
            'harga' => $request->harga,
            'status' => $request->status,
        ]);

        $result = [
            'message' => 'success',
            'error' => 'false',
        ];
        return response($result, 200);
    }

    public function custom_dataBarang(){
        //$request = app()->request;
        $databarang = test_daniel::with('satuan')->get();
        return response($databarang,200);
    }
    public function scopeWithdetail(){
        return $this->with('test_daniel_2');
    }
    // public function satuan(){
    //     return $this->hasOne('App\Models\BasicModels\test_daniel_2','id_barang','id');
    // }

     public function custom_test_collection(){
            $collection1 = collect();
            for ($i = 1; $i <= 10; $i++) {
                $collection1->push([
                    'id' => $i,
                    'name' => 'Person ' . $i,
                    'age' => rand(20, 40),
                ]);
            }

            $collection2 = collect();
            for ($i = 11; $i <= 18; $i++) {
                $collection2->push([
                    'id' => $i,
                    'name' => 'Person ' . $i,
                    'gender' => ($i % 2 == 0) ? 'male' : 'female',
                ]);
            }

            $collection3 = collect();
            for ($i = 19; $i <= 25; $i++) {
                $collection3->push([
                    'id' => $i,
                    'city' => ($i % 2 == 0) ? 'New York' : 'Los Angeles',
                    'zipcode' => ($i % 2 == 0) ? '1000' . $i : '9000' . $i,
                ]);
            }

        // Merge the collections
        $mergedCollection = $collection1->merge($collection2)->merge($collection3);
        $paginate = CollectionHelper::paginate($mergedCollection);
        return $paginate;
    }
}