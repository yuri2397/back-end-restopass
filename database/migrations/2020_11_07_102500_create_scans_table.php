<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->integer("amount"); // montant que l'étudiant vas dépenser
            $table->timestamp("scan_date"); // date d'entrée au resto

            $table->string("user_number"); // numéro de l'étudiant qui entre dans le resto
            $table->foreign("user_number")->references("number")->on("users");

            $table->unsignedBigInteger("resto_id"); // id resto
            $table->foreign("resto_id")->references("id")->on("restos");

            $table->unsignedBigInteger("vigilant_id"); // l'id du vigil qui a scanner le qr-code de l'étudiant
            $table->foreign("vigilant_id")->references("id")->on("vigilants");

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
        Schema::dropIfExists('scans');
    }
}
