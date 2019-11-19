<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatingsAndUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slack_id', 10)->unique()->nullable();
            $table->string('name_first');
            $table->string('name_last');
            $table->string('nickname')->nullable();
            $table->string('email');
            $table->string('password')->nullable();
            $table->timestamp('password_set_at')->nullable();

            $table->timestamp('last_login')->nullable();
            $table->ipAddress('last_login_ip')->default(0);

            $table->string('remember_token', 100)->nullable();

            $table->timestamp('joined_at')->nullable();
            $table->timestamp('cert_checked_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->unsignedTinyInteger('type');
            $table->string('code', 10);
            $table->string('code_long', 10);
            $table->string('name');
            $table->string('name_long');
            $table->unsignedTinyInteger('vatsim_id');
        });

        Schema::create('user_ratings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('rating_id');
            $table->timestamps();
            $table->softDeletes();
        });

        (new RatingsSeeder())->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('user_ratings');
    }
}
