<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'cliente@example.com')->first();

        if ($user) {
            DB::table('clientes')->insert([
                'user_id' => $user->id,
                'nombre' => 'Juan Pérez',
                'telefono' => '0977777777',
                'direccion' => 'Av. Siempre Viva 742',
                'cedula' => '1101234567',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
