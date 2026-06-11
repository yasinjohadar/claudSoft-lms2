<?php

namespace App\Console\Commands;

use App\Services\Storage\StorageInventoryService;
use Illuminate\Console\Command;

class StorageInventoryCommand extends Command
{
    protected $signature = 'storage:inventory
                            {--disk= : Filter by logical disk}
                            {--source= : Filter by source key}
                            {--phase= : Scan a configured migration phase}
                            {--export= : Export results to CSV path}';

    protected $description = 'Scan database file references and report storage locations';

    public function handle(StorageInventoryService $inventoryService): int
    {
        $this->info('Scanning file references...');

        $result = $inventoryService->scan(
            disk: $this->option('disk') ?: null,
            sourceKey: $this->option('source') ?: null,
            phase: $this->option('phase') ?: null,
        );

        $summary = $result['summary'];

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total', $summary['total']],
                ['Cloud only', $summary['cloud_only']],
                ['Local only', $summary['local_only']],
                ['Both', $summary['both']],
                ['Missing', $summary['missing']],
                ['Local-only bytes', number_format((float) $summary['local_only_bytes'])],
            ]
        );

        $exportPath = $this->option('export');

        if ($exportPath) {
            $this->exportCsv($exportPath, $result['items']);
            $this->info("Exported to {$exportPath}");
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    protected function exportCsv(string $path, array $items): void
    {
        $handle = fopen($path, 'w');

        if ($handle === false) {
            $this->error("Could not open {$path} for writing");

            return;
        }

        fputcsv($handle, ['source', 'entity_id', 'disk', 'path', 'status', 'size', 'storage_name']);

        foreach ($items as $item) {
            fputcsv($handle, [
                $item['source_key'] ?? '',
                $item['entity_id'] ?? '',
                $item['disk'] ?? '',
                $item['path'] ?? '',
                $item['status'] ?? '',
                $item['size'] ?? 0,
                $item['storage_name'] ?? '',
            ]);
        }

        fclose($handle);
    }
}
