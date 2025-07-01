<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssinaturasTable {
    public function up() {
        Schema::create('assinaturas', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('cliente_id')->unsigned();
            $table->string('asaas_id', 255);
            $table->string('status', 50);
            $table->string('periodicidade', 20);
            $table->date('start_date');
            $table->date('next_due_date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    public function down() {
        Schema::dropIfExists('assinaturas');
    }
} 