<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstablecimientoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('establecimientos')->insert([
            [
                'nombre' => 'Complejo Deportivo Central',
                'ciudad' => 'Quito',
                'direccion' => 'Av. Amazonas y NNUU',
                'telefono_contacto' => '0999999999',
                'correo_contacto' => 'central@deportes.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cancha Norte',
                'ciudad' => 'Guayaquil',
                'direccion' => 'Calle 123 y Av. Principal',
                'telefono_contacto' => '0988888888',
                'correo_contacto' => 'norte@deportes.com',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
