<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->bigInteger('mobile_number')->nullable()->default(0);
            $table->date('birth_date')->nullable();
            $table->bigInteger('otp')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('google_signup')->nullable()->default(false);
            $table->boolean('facebook_signup')->nullable()->default(false);
            $table->string('profile_pic')->nullable();
            $table->rememberToken();
            $table->text('token')->nullable();
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
        Schema::dropIfExists('users');
    }
}
