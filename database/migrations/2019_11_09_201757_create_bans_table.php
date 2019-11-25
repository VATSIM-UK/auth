<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bans', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('banner_id')->nullable();
            $table->unsignedTinyInteger('reason_id')->nullable();
            $table->unsignedTinyInteger('type');
            $table->string('body')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->timestamp('repealed_at')->nullable();
        });

        Schema::create('ban_reasons', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('name');
            $table->text('body')->nullable();
            $table->string('period');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bans');
        Schema::dropIfExists('ban_reasons');
    }
}
