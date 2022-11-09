<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('organizer_id');
            $table->unsignedBigInteger('meeting_room_id');
            $table->string('title', 255);
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->text('comment')->nullable();
            $table->string('color', 7);
            $table->boolean('is_remind');
            $table->boolean('is_three_days_ago');
            $table->boolean('is_two_days_ago');
            $table->boolean('is_prev_days_ago');
            $table->boolean('is_current_day');
            $table->boolean('is_one_hour_ago');
            $table->boolean('is_half_an_hour_ago');
            $table->boolean('is_ten_minute_ago');
            $table->boolean('is_optional');
            $table->datetime('optional_remind_time')->nullable();
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
        Schema::dropIfExists('reservations');
    }
}
