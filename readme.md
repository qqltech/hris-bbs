Perbedaan dengan versi sebelumnya

# Core Files
- bootstrap/app.php
- app/Helpers/GlobalHelper.php
- app/Http/Controllers/ApiFixedController.php
- app/Http/Controllers/LaradevController.php
- app/Http/Controllers/UserController.php
- routes/*
- config/*
- Providers/*
- templates/*
- resource/views/*
- public/defaults/*
- public/visual.html

# Perbedaan dengan Versi 6.x
## bootstrap/app.php
- penambahan provider Lumen Generator Service Provider dan Migration Generator

## templates/migration.stub
- bigIncrements pakai from(1) dan timestamps

## routes/laradev.php
- password pakai CONFIGPASSWORD