<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class mposisi extends Migration
{
    protected $tableName = "m_posisi";
    
    public function up()
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            //$table->string('_existColumnName_')->change();
            //$table->string('_columnName_');
            //$table->dropColumn([ ]);
            $table->bigInteger('m_comp_id')->comment('{"src":"m_comp.id"}')->nullable()->change();
        });
    }
}
