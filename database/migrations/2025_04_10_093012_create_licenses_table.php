<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->string('secret');
            $table->string('hash');
            $table->boolean('branding_removed')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expired_at')->nullable(); // можна для пробної дати
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
        Schema::dropIfExists('licenses');
    }
};
