<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMembershipTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->boolean('primary')->default(true);
            $table->string('name');
            $table->string('division_expression')->nullable()->comment('Comma-delimited list of divisions that qualify for this membership. * = Any division');
            $table->string('region_expression')->nullable()->comment('Comma-delimited list of regions that qualify for this membership. * = Any region');
            $table->boolean('can_have_secondaries')->default(true);
            $table->integer('priority')->default('99');
        });

        // Insert default memberships
        DB::table('memberships')
            ->insert([
                'identifier' => 'DIV',
                'name' => 'Division',
                'division_expression' => 'GBR',
                'region_expression' => 'EUR',
                'can_have_secondaries' => false,
                'priority' => '0',
            ]);

        DB::table('memberships')
            ->insert([
                [
                    'identifier' => 'REG',
                    'name' => 'Region',
                    'division_expression' => '*',
                    'region_expression' => 'EUR',
                    'priority' => '70',
                ],
                [
                    'identifier' => 'INT',
                    'name' => 'International',
                    'division_expression' => '*',
                    'region_expression' => '*',
                    'priority' => '80',
                ], ]);

        DB::table('memberships')
            ->insert([
                [
                    'identifier' => 'TFR',
                    'primary' => false,
                    'name' => 'Transferring',
                    'priority' => '10',
                ],
                [
                    'identifier' => 'VIS',
                    'primary' => false,
                    'name' => 'Visiting',
                    'priority' => '20',
                ],
            ]);

        // Create pivot table
        Schema::create('user_memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('membership_id');
            $table->string('division')->nullable();
            $table->string('region')->nullable();
            $table->timestamp('started_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('ended_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('user_memberships');
    }
}
