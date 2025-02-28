<?php

namespace App\Console\Commands;

use App\Models\Statement;
use App\Services\StatementSearchService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatementsIndexRange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statements:index-range {min=default} {max=default} {chunk=2000}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index the Statements based on a range';

    /**
     * Execute the console command.
     */
    public function handle(StatementSearchService $statement_search_service): void
    {

        $chunk = (int)$this->argument('chunk');
        $min = $this->argument('min') === 'default' ? DB::table('statements')->selectRaw('MIN(id) AS min')->first()->min : (int)$this->argument('min');
        $max = $this->argument('max') === 'default' ? DB::table('statements')->selectRaw('MAX(id) AS max')->first()->max : (int)$this->argument('max');

        $current = $min;
        while ($current < $max) {
            $range = range($current, $current + ($chunk - 1));
            $statements = Statement::query()->whereIn('id', $range)->get();
            try {
                Log::info('Indexing: ' . $current . " :: " .  $current + ($chunk - 1));
                $statement_search_service->bulkIndexStatements($statements);
            } catch (Exception $e) {
            }
            $current += $chunk;
        }
    }
}
