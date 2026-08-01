<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Geography\Models\Locality;
use App\Domains\Geography\Models\Commune;

class LocalitySeeder extends Seeder
{
    public function run(): void
    {
        try {
            logger()->info('LocalitySeeder: Starting');
            
            $data = $this->parseSqlFile();
            logger()->info('LocalitySeeder: Parsed ' . count($data) . ' localities');
            
            $this->importDirect($data);
            logger()->info('LocalitySeeder: Complete');
        } catch (\Throwable $e) {
            logger()->error('LocalitySeeder ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            logger()->warning('LocalitySeeder: Skipped due to error');
        }
    }

    private function importDirect(array $data): void
    {
        $grouped = collect($data)->groupBy('commune_id');
        
        $rows = [];
        $processedCommunes = 0;

        foreach ($grouped as $communeId => $items) {
            $commune = Commune::where('id', $communeId)->first();

            if (!$commune) {
                logger()->warning("Commune not found: {$communeId}");
                continue;
            }

            $index = 1;
            foreach ($items as $item) {
                $rows[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'commune_id' => $commune->id,
                    'code' => $commune->code . '-' . str_pad($index++, 2, '0', STR_PAD_LEFT),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $processedCommunes++;
            
            // Insert every 50 communes to avoid memory issues
            if ($processedCommunes % 50 === 0) {
                logger()->info("Inserting batch at commune {$processedCommunes}");
                Locality::insert($rows);
                $rows = [];
            }
        }

        // Insert remaining rows
        if (count($rows) > 0) {
            logger()->info("Inserting final batch");
            Locality::insert($rows);
        }

        logger()->info("LocalitySeeder: Imported " . count($data) . " localities");
    }

    private function parseSqlFile(): array
    {
        $path = database_path('seeders/localites.sql');

        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        $content = file_get_contents($path);

        // Parse SQL insert statements
        preg_match_all(
            '/\(\s*(\d+)\s*,\s*"([^"]+)"\s*,\s*(\d+)\s*\)/U',
            $content,
            $matches,
            PREG_SET_ORDER
        );

        $data = [];
        foreach ($matches as $match) {
            $data[] = [
                'id' => (int) $match[1],
                'name' => trim($match[2]),
                'commune_id' => (int) $match[3],
            ];
        }

        return $data;
    }
}
