<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanchaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('canchas')->insert([
            [
                'nombre' => 'Cancha Sintética 1',
                'tipo' => 'futbol',
                'precio_por_hora' => 25.00,
                'descripcion' => 'Cancha con césped sintético de alta calidad.',
                'estado' => true,
                'id_establecimiento' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cancha Techada',
                'tipo' => 'futbol',
                'precio_por_hora' => 30.00,
                'descripcion' => 'Ideal para jugar en días lluviosos.',
                'estado' => true,
                'id_establecimiento' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cancha Techada 1',
                'tipo' => 'padel',
                'precio_por_hora' => 50.00,
                'descripcion' => 'Ideal para jugar en días lluviosos.',
                'estado' => true,
                'id_establecimiento' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Cancha Techada 2',
                'tipo' => 'futbol',
                'precio_por_hora' => 60.00,
                'descripcion' => 'Ideal para jugar en días lluviosos.',
                'estado' => true,
                'id_establecimiento' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
