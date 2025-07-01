<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCobrancasTable
{
    public function up()
    {
        Schema::create('cobrancas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cliente_id')->unsigned();
            $table->string('asaas_id', 255);
            $table->decimal('valor', 10, 2);
            $table->string('status', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cobrancas');
    }
} 