## Global Helper
- Simple Try Catch dengan DB transaction
```php
\DB::beginTransaction();
try{
    //do something
}catch(Exception $e){
    $error  = $e->getMessage()."-".$e.getLine()."-".$e->getFile();
    trigger_error($error);
    \DB::rollback();
}
\DB::commit();
```

- Get Payload Request->all() versi lumen berbentuk object
```php
$payloadAll = req(); //object 
$username   = req('username'); //single key
```

- Download Hasil Query sebagai file xlsx
```php
\Excel::download(new \ExportExcel($queryBuilderGet), \Carbon::now()->format('d-m-Y')."_nama_file.xlsx");
```

- Casting tanggal dan waktu [Carbon Source](https://carbon.nesbot.com/)
```php
$dateToString = \Carbon::now()->format('d-m-Y');
$dateFromString = \Carbon::createFromFormat('d/m/Y', '23/12/2023');
$tomorrow = \Carbon::now()->addDay();
$lastWeek = \Carbon::now()->subWeek();
```


## Custom Models
- Mendapatkan ```Class``` Model Basic
```php
getBasic('nama_model');
```

- Mendapatkan ```Class``` Model Custom
```php
getCustom('nama_model');
```

- Mendapatkan tipe data dari column di model tertentu
```php
getDataType('nama_model','nama_kolom');
```

## Custom API - CRUD

- API:: READ List atau READ by ID dengan mengisikan variable ```$primary_id``` dengan angka integer dari Primary Id row. Hasil ada di index ```$dataAll['data']```, sisanya pagination, dll.
Untuk ```$params``` bisa diberi inisiasi array empty yakni ```[]```
```php
$dataAll = Api()->readOperation('nama_model',$params=[
        "where_raw"     =>  null,
        "order_by"      =>  'updated_at',
        "order_type"    =>  'DESC',
        "order_by_raw"  =>  null,
        "search"        =>  null,
        "searchfield"   =>  null,
        "selectfield"   =>  null,
        "paginate"      =>  25,
        "page"          =>  1,
        "join"          =>  true,
        "joinMax"       =>  0,
        "addSelect"     =>  null,
        "addJoin"       =>  null,
        "group_by"      =>  null
    ],$primary_id = null);
$data = $dataAll['data'];
```

- API:: CREATE create data baik single row, maupun cascade detail-subdetail
```php
$data = [
    'nama'=>'fajar firmansyah',
    'nomor'=>1001,
    'details_hobi'[
        [
            'hobi' => 'Makan'
        ],
        [
            'hobi' => 'Tidur'
        ]
    ]
];
Api()->createOperation( "nama_model", $data, null, null);
```

- API:: UPDATE update data baik single row, maupun cascade detail-subdetail dengan ```$primary_id``` integer
```php
$data = [
    'nama'=>'fajar firmansyah',
    'nomor'=>1001,
    'details_hobi'[
        [
            'hobi' => 'Makan'
        ],
        [
            'hobi' => 'Tidur'
        ]
    ]
];
Api()->updateOperation( "nama_model", $data, $primary_id, null);
```

- API:: DELETE remove data baik single row, maupun cascade detail-subdetail dengan ```$primary_id``` integer
```php
Api()->deleteOperation( "nama_model", null, $primary_id, null);
```

## Emails

- Send Email pengiriman email secara synchronous
```php 
SendEmail("email@domain.com","Subject Anda","<a href='#'>Contoh Link</a>");
```

## Debugging

- Menyimpan Array ke JSON File sebagai {appRoot}/logs/{namamodel}.json bisa berguna untuk ditampilkan di panel backend editor di bagian Log
```php 
$arrayData = req(); //contoh: logging request data dengan helper req()
setLog($arrayData);
```

- Memanggil Hasil Simpanan Log {appRoot}/logs/{namamodel}.json
```php 
$debugLog = getLog();
```

- Menghapus File Log {appRoot}/logs/{namamodel}.json
```php 
removeLog();
```

- Console Debugging, pengiriman hasil debug ke console browser (Javascript) secara websocket realtime pengganti ```dd``` punya laravel
```php 
ff($any_data,"your_title");
```

- Mendapatkan jenis route yang diakses user apakah ```'read_list'``` atau ```'read_id'```
```php
getRoute(); //atau
$hasilBool = isRoute('read_list'); //true atau false
```

## Reports

- Reporting HTML, PDF, & XLSX, belajar template bisa ke [link](https://dejozz.com/report.html)
```php
$template = "string dari disain excel, bisa dari hasil yg telah disimpan di database";
$data = [
    "from" => "01/12/2020",
    "to" => "01/12/2020",
    "Type Laporan" => "Tahunan",
    "data" => [ //bisa didapatkan dari hasil DB query,Model Get, atau Array Data
        ['netto'=>2000,'rugi'=>0,'untung'=>3000,'minggu-ke'=>1],
        ['netto'=>4000,'rugi'=>2,'untung'=>5000,'minggu-ke'=>2]
    ]
];
$output = "html"; //contoh jika ingin outputnya html, sisanya: pdf dan xlsx
if( strpos(strtolower($output),"htm")!==false ){
    return renderHTML($template,$data,["break"=>false,"title"=>"Laporan", "orientation"=>"L","size"=>"A4","fontsize"=>10]);
}elseif( strpos(strtolower($output),"pdf")!==false ){
    return renderPDF($template,$data,["break"=>false,"title"=>"Laporan", "orientation"=>"L","fontsize"=>10]); 
}else{
    return renderXLS($template,$data,["break"=>false,"sheetname"=>"minggu-ke","title"=>"Laporan", "orientation"=>"L","fontsize"=>10]);
}
```
