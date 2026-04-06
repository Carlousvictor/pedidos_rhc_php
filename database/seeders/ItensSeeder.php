<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItensSeeder extends Seeder
{
    public function run(): void
    {
        $sqlFile = base_path('../supabase/seed_itens.sql');

        if (!file_exists($sqlFile)) {
            $this->command->error("seed_itens.sql not found at: $sqlFile");
            return;
        }

        $content = file_get_contents($sqlFile);

        // Extract the VALUES portion from the INSERT statement
        if (!preg_match('/VALUES\s*\n(.*);$/s', $content, $match)) {
            $this->command->error("Could not parse INSERT VALUES from seed file.");
            return;
        }

        $valuesBlock = $match[1];

        // Parse each row: ('val1', 'val2', 'val3', 'val4')
        preg_match_all("/\('([^']*)',\s*'([^']*)',\s*'((?:[^'\\\\]|\\\\'|'')*)',\s*'([^']*)'\)/", $valuesBlock, $matches, PREG_SET_ORDER);

        $batch = [];
        $count = 0;

        foreach ($matches as $m) {
            $nome = str_replace("''", "'", $m[3]);

            $batch[] = [
                'id' => Str::uuid()->toString(),
                'codigo' => $m[1],
                'referencia' => $m[2],
                'nome' => $nome,
                'tipo' => $m[4],
            ];

            $count++;

            if (count($batch) >= 100) {
                DB::table('itens')->insert($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('itens')->insert($batch);
        }

        $this->command->info("Imported {$count} items.");
    }
}
