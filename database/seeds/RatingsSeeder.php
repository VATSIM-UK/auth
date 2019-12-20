<?php

use App\Constants\RatingTypeConstants;
use Illuminate\Database\Seeder;

class RatingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Rating::insert([
            ['code' => 'OBS', 'type' => RatingTypeConstants::ATC, 'code_long' => 'OBS', 'name' => 'Observer', 'name_long' => 'Observer', 'vatsim_id' => 1],
            ['code' => 'S1', 'type' => RatingTypeConstants::ATC, 'code_long' => 'STU', 'name' => 'Student 1', 'name_long' => 'Ground Controller', 'vatsim_id' => 2],
            ['code' => 'S2', 'type' => RatingTypeConstants::ATC, 'code_long' => 'STU2', 'name' => 'Student 2', 'name_long' => 'Tower Controller', 'vatsim_id' => 3],
            ['code' => 'S3', 'type' => RatingTypeConstants::ATC, 'code_long' => 'STU+', 'name' => 'Student 3', 'name_long' => 'Approach Controller', 'vatsim_id' => 4],
            ['code' => 'C1', 'type' => RatingTypeConstants::ATC, 'code_long' => 'CTR', 'name' => 'Controller 1', 'name_long' => 'Area Controller', 'vatsim_id' => 5],
            ['code' => 'C2', 'type' => RatingTypeConstants::ATC, 'code_long' => 'CTR+', 'name' => 'Senior Controller', 'name_long' => 'Senior Controller', 'vatsim_id' => 6],
            ['code' => 'C3', 'type' => RatingTypeConstants::ATC, 'code_long' => 'CTR+', 'name' => 'Senior Controller', 'name_long' => 'Senior Controller', 'vatsim_id' => 7],

            ['code' => 'I1', 'type' => RatingTypeConstants::TRAINING_ATC, 'code_long' => 'INS', 'name' => 'Instructor', 'name_long' => 'Instructor', 'vatsim_id' => 8],
            ['code' => 'I2', 'type' => RatingTypeConstants::TRAINING_ATC, 'code_long' => 'INS+', 'name' => 'Senior Instructor', 'name_long' => 'Senior Instructor', 'vatsim_id' => 9],
            ['code' => 'I3', 'type' => RatingTypeConstants::TRAINING_ATC, 'code_long' => 'INS+', 'name' => 'Senior Instructor', 'name_long' => 'Senior Instructor', 'vatsim_id' => 10],

            ['code' => 'SUP', 'type' => RatingTypeConstants::ADMIN, 'code_long' => 'SUP', 'name' => 'Supervisor', 'name_long' => 'Network Supervisor', 'vatsim_id' => 11],
            ['code' => 'ADM', 'type' => RatingTypeConstants::ADMIN, 'code_long' => 'ADM', 'name' => 'Administrator', 'name_long' => 'Network Administrator', 'vatsim_id' => 12],

            ['code' => 'P1', 'type' => RatingTypeConstants::PILOT, 'code_long' => 'P1', 'name' => 'P1', 'name_long' => 'Online Pilot', 'vatsim_id' => 1],
            ['code' => 'P2', 'type' => RatingTypeConstants::PILOT, 'code_long' => 'P2', 'name' => 'P2', 'name_long' => 'Flight Fundamentals', 'vatsim_id' => 2],
            ['code' => 'P3', 'type' => RatingTypeConstants::PILOT, 'code_long' => 'P3', 'name' => 'P3', 'name_long' => 'VFR Pilot', 'vatsim_id' => 4],
            ['code' => 'P4', 'type' => RatingTypeConstants::PILOT, 'code_long' => 'P4', 'name' => 'P4', 'name_long' => 'IFR Pilot', 'vatsim_id' => 8],
            ['code' => 'P6', 'type' => RatingTypeConstants::PILOT, 'code_long' => 'P6', 'name' => 'P6', 'name_long' => 'P6', 'vatsim_id' => 32],
            ['code' => 'P7', 'type' => RatingTypeConstants::PILOT, 'code_long' => 'P7', 'name' => 'P7', 'name_long' => 'P7', 'vatsim_id' => 64],
            ['code' => 'P8', 'type' => RatingTypeConstants::PILOT, 'code_long' => 'P8', 'name' => 'P8', 'name_long' => 'P8', 'vatsim_id' => 128],
            ['code' => 'P9', 'type' => RatingTypeConstants::PILOT, 'code_long' => 'P9', 'name' => 'P9', 'name_long' => 'Pilot Flight Instructor', 'vatsim_id' => 256],
        ]);
    }
}
