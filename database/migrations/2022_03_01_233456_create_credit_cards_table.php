<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("name");
            $table->string("type");
            $table->string("number")->index();
            $table->string("expirationDate");
            $table->string("email")
                ->references("email")
                ->on("account_holders")
                ->onDelete('cascade');
            $table->string("account_number")
                ->references("account")
                ->on("account_holders")
                ->onDelete('cascade');
            $table->index(['number', 'email', 'account_number']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_cards');
    }
};
