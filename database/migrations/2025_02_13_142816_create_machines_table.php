<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->enum('status', ['idle', 'processing'])->default('idle');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('machines');
    }
};
