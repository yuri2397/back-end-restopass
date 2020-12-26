<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("amount"); // le montant de la transaction
            $table->timestamp("date_transfert"); // date heure de la transaction
            $table->string("forwarder_number"); // numero de dossier de l'expediteur
            $table->string("recipient_number"); // numero de dossier du receveur
            $table->foreign("forwarder_number")->references("number")->on("users")->onDelete("cascade");
            $table->foreign("recipient_number")->references("number")->on("users")->onDelete("cascade");
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
        Schema::dropIfExists('transfers');
    }
}
