<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restos', function (Blueprint $table) {
            $table->id();
            $table->string("code")->unique();
            $table->string("name");
            $table->unsignedBigInteger("created_by");
            $table->unsignedBigInteger("updated_by");
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
        Schema::dropIfExists('resto');
    }
}
