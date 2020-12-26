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
        Schema::create('users', function (Blueprint $table) {//Etudiant
            $table->string('number')->unique();// numero de dossier de l'étudiant
            $table->string('first_name'); // prénom de l'étudiant
            $table->string('last_name'); // nom de l'étudiant
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable(); // date de validation de l'email
            $table->string('password'); // mot de passe du compte étudiant
            $table->bigInteger('pay')->default(0); // le solde de l'étudiant
            $table->unsignedBigInteger('establishment_id'); // l'établissement de l'étudiant
            $table->foreign('establishment_id')->references('id')->on('establishments')->onDelete("cascade");
            $table->unsignedBigInteger("created_by");
            $table->unsignedBigInteger("updated_by");
            $table->rememberToken();
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
