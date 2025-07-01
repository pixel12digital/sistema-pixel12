<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaturasTable {
    public function up() {
        Schema::create('faturas', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('cliente_id')->unsigned();
            $table->string('asaas_id', 255);
            $table->decimal('valor', 10, 2);
            $table->string('status', 50);
            $table->string('invoice_url', 255);
            $table->date('due_date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    public function down() {
        Schema::dropIfExists('faturas');
    }
} 