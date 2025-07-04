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
        Schema::create('canchas', function (Blueprint $table) {
            $table->id('id_cancha');
            $table->string('nombre');
            $table->enum('tipo', ['futbol', 'padel']);
            $table->decimal('precio_por_hora', 8, 2);
            $table->text('descripcion')->nullable();
            $table->boolean('estado')->default(true);
            $table->foreignId('id_establecimiento')->constrained('establecimientos', 'id_establecimiento')->cascadeOnDelete();
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
        Schema::dropIfExists('canchas');
    }
};
