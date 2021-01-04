## Create Before
- Fungsi ini dijalankan ketika sebuah model <b>AKAN</b> digunakan untuk ```create``` data
```php
public function createBefore($model, $arrayData, $metaData, $id=null)
{
    $newModel = $model;
    $newArrayData  = array_merge($arrayData,[]);
    return [
        "model"  => $newModel,
        "data"   => $newArrayData,
        // 'errors' => ['error1','error2'] //untuk menggagalkan create data
    ];
}
```

## Create After
- Fungsi ini dijalankan ketika sebuah model <b>TELAH</b> digunakan untuk ```create``` data
```php
public function createAfter($model, $arrayData, $metaData, $id=null)
{        
    //kode anda, misal notifikasi ke email
}
```

## Update Before
- Fungsi ini dijalankan ketika sebuah model <b>AKAN</b> digunakan untuk ```update``` data
```php
public function updateBefore($model, $arrayData, $metaData, $id=null)
{
    $newModel = $model;
    $newArrayData  = array_merge($arrayData,[]);
    return [
        "model"  => $newModel,
        "data"   => $newArrayData,
        // 'errors' => ['error1','error2'] //untuk menggagalkan update data
    ];
}
```

## Update After
- Fungsi ini dijalankan ketika sebuah model <b>TELAH</b> digunakan untuk ```update``` data
```php
public function updateAfter($model, $arrayData, $metaData, $id=null)
{
    //kode anda
}

## Delete Before
- Fungsi ini dijalankan ketika sebuah model <b>AKAN</b> digunakan untuk ```delete``` data
```php
public function deleteBefore($model, $arrayData, $metaData, $id=null)
{
    $newModel = $model;
    return [
        "model" => $newModel
    ];
}
```

## Delete After
- Fungsi ini dijalankan ketika sebuah model <b>TELAH</b> digunakan untuk ```delete``` data
```php
public function deleteAfter($model, $arrayData, $metaData, $id=null)
{
    //kode anda
}
```

## Create After Transaction
- Fungsi ini dijalankan ketika sebuah model <b>TELAH SELESAI</b> digunakan untuk ```create``` data secara full-cascade
```php
public function createAfterTransaction($newdata, $olddata, $data, $meta)
{
    //kode anda
}
```

## Update After Transaction
- Fungsi ini dijalankan ketika sebuah model <b>TELAH SELESAI</b> digunakan untuk ```update``` data secara full-cascade
```php
public function updateAfterTransaction($newdata, $olddata, $data, $meta)
{
    //kode anda
}
```

## Transform Data Per Row
- Fungsi ini dijalankan setiap baris row data. Dapat digunakan untuk menyusun array per row data response
```php
public function transformRowData($row)
{
    return array_merge($row,[
        'key_tambahan' => 'value'
    ]);
}
```

## Transform Data Array
- Fungsi ini dijalankan terakhir setelah fungsi transformRow, digunakan untuk transform array keseluruhan data sebelum dijadikan response ke klien
```php
public function transformArrayData($arrayData)
{
    return array_map(function($row){
        $row['key_tambahan'] => 'value';
        return $row;
    },$arrayData);
}
```