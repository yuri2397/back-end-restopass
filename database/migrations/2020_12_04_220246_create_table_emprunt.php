<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableEmprunt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_emprunt', function (Blueprint $table) {
            $table->id();
            $table->string('user_number');
            $table->foreign("user_number")->references("number")->on("users");
            $table->timestamp('date_emprunt');
            $table->boolean('state')->default(false);
            $table->integer('amount');
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
        Schema::dropIfExists('table_emprunt');
    }
}
