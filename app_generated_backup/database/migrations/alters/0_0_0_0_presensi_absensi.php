<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class presensiabsensi extends Migration
{
    protected $tableName = "presensi_absensi";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            $table->dropColumn(['catatan']);
            // $table->string('checkin_foto')->nullable()->change();
            // $table->string('checkout_foto')->nullable()->change();
            // $table->text('catatan_in')->nullable();
            // $table->text('catatan_out')->nullable();
        });
    }
}
