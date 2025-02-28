<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\Statement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class StatementStatsService
{
    public function totalForPlatformAndRange(Platform $platform, Carbon $start, Carbon $end): int
    {
        $days_result = DB::table('statements')
                         ->join('platforms', 'platforms.id', '=', 'statements.platform_id')
                         ->selectRaw('count(statements.id) as statements_count')
                         ->where('platforms.id', $platform->id)
                         ->where('statements.created_at', '>=', $start->format('Y-m-d 00:00:00'))
                         ->where('statements.created_at', '<=', $end->format('Y-m-d 23:59:59'))
                         ->get();
        return $days_result->first()->statements_count ?? 0;
    }

    public function dayCountsForPlatformAndRange(Platform $platform, Carbon $start, Carbon $end, bool $reverse = true): array
    {
        $date_counts = [];
        $days_result = DB::table('statements')
                         ->join('platforms', 'platforms.id', '=', 'statements.platform_id')
                         ->selectRaw('count(statements.id) as statements_count, DATE(statements.created_at) as created_at_date')
                         ->groupByRaw('DATE(statements.created_at)')
                         ->where('platforms.id', $platform->id)
                         ->where('statements.created_at', '>=', $start->format('Y-m-d 00:00:00'))
                         ->where('statements.created_at', '<=', $end->format('Y-m-d 23:59:59'))
                         ->get();

        $highest = -1;
        while($start < $end)
        {
            $d = $start->format('Y-m-d');
            $c = $days_result->firstWhere('created_at_date', $d)->statements_count ?? 0;
            $date_counts[] = [
                'date' => $start->clone(),
                'count' => $c
            ];
            if ($c > $highest) {
                $highest = $c;
            }
            $start->addDay();
        }
        if ($highest < 1)
        {
            $highest = 1;
        }
        foreach ($date_counts as $index => $date_count)
        {
            $date_counts[$index]['percentage'] = (int) ceil( ($date_count['count'] / $highest) * 100 );
        }

        if ($reverse) {
            $date_counts = array_reverse($date_counts);
        }
        return $date_counts;
    }

    public function countForPlatform(Platform $platform): int
    {
        return DB::table('statements')
             ->join('platforms', 'platforms.id', '=', 'statements.platform_id')
             ->selectRaw('count(statements.id) as statements_count')
             ->where('platforms.id', $platform->id)
             ->get()->first()->statements_count;
    }

    public function countForPlatformSince(Platform $platform, Carbon $since): int
    {
        return DB::table('statements')
             ->join('platforms', 'platforms.id', '=', 'statements.platform_id')
             ->selectRaw('count(statements.id) as statements_count')
             ->where('platforms.id', $platform->id)
             ->where('statements.created_at', '>=', $since->format('Y-m-d 00:00:00'))
             ->get()->first()->statements_count;
    }

    public function totalStatements()
    {
        return Statement::count();
    }

    public function attributeCountForPlatform(Platform $platform, string $attribute, $value): int
    {
        return DB::table('statements')
                 ->join('platforms', 'platforms.id', '=', 'statements.platform_id')
                 ->selectRaw('count(statements.id) as statements_count')
                 ->where('platforms.id', $platform->id)
                 ->where($attribute, $value)
                 ->get()->first()->statements_count;
    }
}
