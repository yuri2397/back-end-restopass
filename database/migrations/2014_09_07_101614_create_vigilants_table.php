<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVigilantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vigilants', function (Blueprint $table) {
            $table->id();// nom
            $table->string("code")->unique(); // code pour identifier les vigils
			$table->string("first_name");
			$table->string("last_name");
            $table->unsignedBigInteger('resto_id');
            $table->foreign('resto_id')->references('id')->on('restos')->onDelete('cascade');
			$table->string("password");
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
        Schema::dropIfExists('vigilants');
    }
}
