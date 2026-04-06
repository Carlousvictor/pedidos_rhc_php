<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Unidades (from original system)
        $unidades = [
            'HOSPITAL CASA EVANGELICO',
            'HOSPITAL CASA SAO BERNARDO',
            'HOSPITAL CASA DE PORTUGAL',
            'HOSPITAL CASA MENSSANA',
            'HOSPITAL CASA ILHA DO GOVERNADOR',
            'HOSPITAL CASA RIO LARANJEIRAS',
            'HOSPITAL CASA RIO BOTAFOGO',
            'OFTALMOCASA',
            'HOSPITAL CASA SANTA CRUZ',
            'HOSPITAL CASA PREMIUM',
        ];

        $unidadeIds = [];
        foreach ($unidades as $nome) {
            $id = Str::uuid()->toString();
            $unidadeIds[$nome] = $id;
            DB::table('unidades')->insert([
                'id' => $id,
                'nome' => $nome,
            ]);
        }

        // Admin user
        DB::table('usuarios')->insert([
            'id' => Str::uuid()->toString(),
            'username' => 'admin',
            'password_hash' => 'Rc2026#@',
            'nome' => 'Administrador do Sistema',
            'role' => 'admin',
            'unidade_id' => null,
            'permissoes' => json_encode([
                'scope' => 'admin',
                'modulos' => [
                    'criar_pedido' => true,
                    'pedidos' => true,
                    'historico' => true,
                    'itens' => true,
                    'relatorios' => true,
                    'transferencias' => true,
                    'usuarios' => true,
                    'notas_fiscais' => true,
                ],
            ]),
            'created_at' => now(),
        ]);

        // Comprador user
        DB::table('usuarios')->insert([
            'id' => Str::uuid()->toString(),
            'username' => 'comprador',
            'password_hash' => 'comprador123',
            'nome' => 'Comprador RHC',
            'role' => 'comprador',
            'unidade_id' => null,
            'permissoes' => json_encode([
                'scope' => 'admin',
                'modulos' => [
                    'criar_pedido' => true,
                    'pedidos' => true,
                    'historico' => true,
                    'itens' => true,
                    'relatorios' => true,
                    'transferencias' => true,
                ],
            ]),
            'created_at' => now(),
        ]);
    }
}
