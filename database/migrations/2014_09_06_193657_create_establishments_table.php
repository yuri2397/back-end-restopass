<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstablishmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('establishments', function (Blueprint $table) {
            $table->id();
            $table->string("code")->unique();
            $table->string('name')->unique();// nom de l'établissement
            $table->boolean("state")->default(false); // true si y'a sans ticket, false sinon
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
        Schema::dropIfExists('establishments');
    }
}
