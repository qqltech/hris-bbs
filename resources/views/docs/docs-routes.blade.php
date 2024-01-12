## Basic
### Register
Mendaftarkan user dengan beberapa payload seperti name,email,password, dan password konfirmasi
  ><b style='color:blue;'>[POST]</b> ```{{url('/register')}}```
### Login
Mengirim username/email dengan password yang valid
  ><b style='color:blue;'>[POST]</b> ```{{url('/login')}}```
### Get Me
Berguna untuk mendapatkan informasi diri sendiri pasca login
  ><b style='color:green;'>[GET]</b> ```{{url('/me')}}```

## REST Api Standard
### Read List
Route untuk melakukan request untuk keperluan membaca list resource dalam suatu model
  ><b style='color:green;'>[GET]</b> ```{{url('/operation/{model}')}}```
### Read by id
Route untuk melakukan request untuk keperluan membaca 1 resource dalam suatu model dengan primary key tertentu
  ><b style='color:green;'>[GET]</b> ```{{url('/operation/{model}/{id}')}}```
### Create
Route untuk melakukan request untuk keperluan membuat resource baru dengan primary key tertentu
  ><b style='color:blue;'>[POST]</b> ```{{url('/operation/{model}/{id}')}}```
### Update
Route untuk melakukan request untuk keperluan memperbarui resource baru dengan primary key tertentu
  ><b style='color:brown;'>[PUT]</b> ```{{url('/operation/{model}/{id}')}}```
### Update V2
Route untuk melakukan request untuk keperluan memperbarui resource baru* dengan primary key tertentu
  ><b style='color:brown;'>[PATCH]</b> ```{{url('/operation/{model}/{id}')}}```
### Delete
Route untuk melakukan request untuk keperluan menghapus resource dengan primary key tertentu
  ><b style='color:red;'>[DELETE]</b> ```{{url('/operation/{model}/{id}')}}```

## Route Functions
### Custom Function
Baris Kode Custom Function bisa ditulis di custom model manapun. Format nama fungsi: custom_<b>namafungsi</b>
```php
public function custom_namafungsi($request){
    return 'custom method ok';
}
```
Cara mengakses:
><b style='color:green;'>[GET]</b> ```{{url('/operation/{model}/namafungsi')}}```<br>
><b style='color:green;'>[POST]</b> ```{{url('/operation/{model}/namafungsi')}}```

### Public Function
Baris Kode Public Function ( bisa diakses oleh klien tanpa otorisasi/auth ) di custom model manapun. Format nama fungsi: public_<b>namafungsi</b>
```php
public function public_namafungsi($request){
    return 'public method ok';
}
```
Cara mengakses:
><b style='color:green;'>[GET]</b> ```{{url('/public/{model}/namafungsi')}}```<br>
><b style='color:green;'>[POST]</b> ```{{url('/public/{model}/namafungsi')}}```
