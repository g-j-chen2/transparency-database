<?php

namespace App\Console\Commands;

use App\Jobs\StatementCsvExportZip;
use App\Services\DayArchiveService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExportDateZipCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exportcsv:zipcsv {date=yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'zip the csv files.';

    /**
     * Execute the console command.
     */
    public function handle(DayArchiveService $day_archive_service): void
    {
        $date = $this->argument('date');

        if ($date === 'yesterday') {
            $date = Carbon::yesterday();
        } else {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $date);
            } catch (Exception $e) {
                $this->error('Issue with the date provided, checked the format yyyy-mm-dd');
                return;
            }
        }

        $exports = $day_archive_service->buildBasicArray();

        foreach ($exports as $export) {
            StatementCsvExportZip::dispatch($date->format('Y-m-d'), $export['slug'], 'full');
            StatementCsvExportZip::dispatch($date->format('Y-m-d'), $export['slug'], 'light');
        }
    }
}
