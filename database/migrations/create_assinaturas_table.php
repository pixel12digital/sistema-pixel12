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

        // Migration para tabela de mensagens de cobrança personalizadas
        $mysqli->query("CREATE TABLE IF NOT EXISTS mensagens_cobranca (
          id INT AUTO_INCREMENT PRIMARY KEY,
          canal_id INT NOT NULL,
          tipo VARCHAR(32) NOT NULL,
          mensagem TEXT NOT NULL,
          UNIQUE KEY (canal_id, tipo),
          FOREIGN KEY (canal_id) REFERENCES canais_comunicacao(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }

    public function down() {
        Schema::dropIfExists('assinaturas');
    }
} 