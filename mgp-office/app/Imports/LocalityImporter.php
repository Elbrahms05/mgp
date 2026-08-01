<?php

namespace App\Imports;

use App\Domains\Geography\Models\Locality;
use App\Domains\Geography\Models\Commune;
use Illuminate\Support\Facades\DB;

class LocalityImporter
{
    public function import(array $data): void
    {
        try {
            logger()->info('Starting Locality import', ['total_data' => count($data)]);
            
            $rows = [];
            $grouped = collect($data)->groupBy('commune_id');
            
            logger()->info('Data grouped by commune', ['groups' => count($grouped)]);

            $processedCount = 0;
            foreach ($grouped as $communeId => $items) {
                try {
                    $commune = Commune::where('id', $communeId)->first();

                    if (!$commune) {
                        logger()->warning("Commune introuvable", ['commune_id' => $communeId]);
                        continue;
                    }

                    $index = 1;
                    foreach ($items as $item) {
                        $rows[] = [
                            'id' => $item['id'],
                            'name' => $item['name'],
                            'commune_id' => $commune->id,
                            'code' => $commune->code . '-' . str_pad($index, 2, '0', STR_PAD_LEFT),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $index++;
                    }
                    
                    $processedCount++;
                    if ($processedCount % 50 === 0) {
                        logger()->info('Processed communes', ['count' => $processedCount, 'rows_so_far' => count($rows)]);
                    }
                } catch (\Throwable $e) {
                    logger()->error("Error processing commune_id: {$communeId}", [
                        'error' => $e->getMessage(),
                        'line' => $e->getLine(),
                    ]);
                    // Continue with next commune
                }
            }

            logger()->info('Rows preparation complete', ['total_rows' => count($rows), 'communes_processed' => $processedCount]);

            if (count($rows) > 0) {
                // Insert in chunks to avoid memory issues
                $chunks = array_chunk($rows, 1000);
                logger()->info('Starting chunked insert', ['chunks_count' => count($chunks)]);
                
                DB::transaction(function () use ($chunks) {
                    foreach ($chunks as $index => $chunk) {
                        Locality::insert($chunk);
                        logger()->debug("Inserted chunk " . ($index + 1));
                    }
                });
                
                logger()->info('Localities imported successfully', ['total_count' => count($rows)]);
            }
        } catch (\Throwable $e) {
            logger()->error('Fatal error in Locality import', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }
}
