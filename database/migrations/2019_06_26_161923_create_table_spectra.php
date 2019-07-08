<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSpectra extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spectras', function (Blueprint $table) {
            $table->bigIncrements('spectra_id');
            $table->string('device_name')->index();
            $table->integer('ID')->unsigned();
            $table->datetime('time_stamp');
            $table->double('aux', 20, 8);
            $table->double('pwr', 20, 8);
            $table->integer('boost_pump')->unsigned();
            $table->string('silabs_3_3');
            $table->string('tmp_1');
            $table->string('tmp_2');
            $table->string('bat_plus');
            $table->string('p_1');
            $table->string('p_2');
            $table->string('p_3');
            $table->string('p_4');
            $table->string('bp');
            $table->string('fp');
            $table->string('tnk_lvl_1');
            $table->string('tnk_lvl_2');
            $table->double('ph', 20, 8);
            $table->string('reg_5v');
            $table->string('sal_1');
            $table->string('sal_2');
            $table->string('tsh');
            $table->string('tsl');
            $table->string('trigger');
            $table->string('lockout');
            $table->string('flow_1');
            $table->string('flow_2');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            // add unique on device_name and time_stamp
            $table->unique(['device_name', 'time_stamp']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spectras');
    }
}
