
## Requirements
Berikut adalah kebutuhan minimal baik teknologi maupun kemampuan programmer yang harus dipenuhi sebelum dapat menggunakan backend-custom Lumen berjudul <b>LARAHAN</b>.

### Server Requirements
- OS : GNU Linux / Microsoft Windows
- Webserver : Apache 2.0 / Nginx / LiteSpeed
- Database : MySQL 7+ / MariaDB 10.3+ / PostgreSQL 11+
- Pemgrograman : PHP 7.2+
- Composer PHP [Download & Install](https://getcomposer.org)
- Git Client [Download & Install](https://git-scm.com/)
- Default HTTP/HTTPS Port, Custom Port 9001
- Bitnami WAPP/XAMPP untuk paket software Webserver dan database Postgresql/Mysql/MariaDB

### Framework & Additional Libraries
- PHP Framework: Lumen 6.x [Docs](https://lumen.laravel.com)
- Library untuk otentikasi Oauth 2 Api Auth [Docs](https://laravel.com/docs/master/passport)
- Library Excel untuk export/import data [Docs](https://docs.laravel-excel.com/3.1/getting-started)
- Library untuk DML/DDL ke Database [Docs](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest)
- Library Deteksi IP Klien [Github](https://github.com/stevebauman/location)
- Library Ekstensi Query Common Table (CTE)  [Packagist](https://github.com/staudenmeir/laravel-cte)


### Basic Skills
- PHP Native [Tutorial](https://www.w3schools.com/php/DEFAULT.asp)
- Pengetahuan <i>Model-View-Controller</i> [Tutorial](https://www.tutorialspoint.com/mvc_framework/mvc_framework_introduction.htm)
- Pengetahuan <i>Object Oriented Programming</i> dalam PHP [Tutorial](https://www.w3schools.com/php/php_oop_classes_objects.asp)
- Javascript <i>XHR Ajax Http Request </i> [Tutorial](https://www.w3schools.com/js/js_ajax_http.asp)
- Struktur Relational Database (ERD, CDM, PDM) [Tutorial](https://www.tutorialspoint.com/dbms/index.htm)
- Basic CRUD Query SQL [Tutorial](https://www.tutorialspoint.com/sql/sql-syntax.htm)
- Basic Migrations & Eloquent ORM/DB Query Laravel [Tutorial](https://laravel.com/docs)
- Pengetahuan dasar <i>Git Client</i> [Tutorial](https://git-scm.com/book/id)
- Pengetahuan dasar editor kode <i>VSCode</i> atau <i>Sublime Text</i>
- Penggunaan dasar <i>Postman Api Client</i> [Download & Tutorial](https://www.postman.com/)
- Pengertian dasar <i>Backend & Frontend Scoping</i> [Tutorial](https://blog.udacity.com/2014/12/front-end-vs-back-end-vs-full-stack-web-developers.html)


## Step By Step
Berikut Langkah-langkah untuk melakukan instalasi dan konfigurasi.

### Persiapan
- Biasakan berdoa kepada Tuhan YME sebelum memulai pekerjaan apapun :bowtie:
- Buka laptop atau <i>Personal Computer</i> dan nyalakan
- Pastikan komputer terkoneksi dengan internet :100:

### Clone Repositori
- Masuklah ke direktori yang akan digunakan untuk cloning repositori, bagi yang ingin menggunakan <b>/public</b> folder dari webserver langsung silahkan 
masuk ke direktori publicnya langsung, misal di Apache2 yakni <b>/var/www/html</b> (linux) atau <b>/htdocs</b> bagi pengguna XAMPP dan WAPP
- Buat akun di www.gitlab.com dan pastikan menghubungi akun gitlab @starlight93 agar mendapat izin untuk cloning
- Lakukan clone via HTTPS dengan port jaringan 443 dengan perintah berikut:
> git clone https://gitlab.com/starlight93/larahan.git
- Jika muncul prompt username dan password silahkan diisikan
- Bagi user yang telah mendaftarkan SSH-key ke gitlab, bisa melakukan cloning via SSH dengan port jaringan 22 berikut:
> git clone git@gitlab.com:starlight93/larahan.git
- Jika telah selesai cloning, jangan tutup terminal atau CMD

### One Click Installation
- Masuklah ke direktori hasil clone repositori dengan perintah <i>Change Directory</i>
> cd larahan
- Pastikan koneksi internet lancar, dan tunggu sampai proses selesai
- Bagi pengguna linux bisa menggunakan bash script di dalam direktori tersebut dengan perintah:
> . linuxinstall.sh
- Bagi pengguna windows bisa menggunakan bash script di dalam direktori tersebut dengan perintah:
> wininstall.bat


### Post Installation
- Setelah proses instalasi selesai, nyalakan webserver misal Apache/Nginx/LiteSpeed 
- Buka browser, bagi pengguna chrome silahkan install dan aktifkan extension JSON seperti [ini](https://chrome.google.com/webstore/detail/jsonview/chklaanhfefbnpoihckbnefhakgolnmc?hl=en)
- Akses URL dengan alamat berikut
> http://localhost/larahan
- Tampilan yang anda lihat harus berupa JSON seperti yang anda lihat di [sini]({{url('/')}})


### Deployment
- Teknis dari deployment maupun development local adalah sama saja seperti di atas
- Khusus pengguna Linux, jika telah memasuki tahap production jangan lupa untuk mengembalikan Hak Akses File dan direktori berikut
```mermaid
graph LR
    chmod_ke_755-->.env
    chmod_ke_755-->/public
    chmod_ke_755-->/database/migrations
    chmod_ke_755-->/app/Models
``` 
- Untuk optimasi backend, beberapa faktor tentunya pasti ada optimasi, dimulai dari .htaccess file, jaringan, port, dan lainnya.