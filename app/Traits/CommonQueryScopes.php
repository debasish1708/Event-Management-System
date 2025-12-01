<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait CommonQueryScopes
{
    /**
     * Scope: search by title (case-insensitive LIKE).
     */
    public function scopeSearchByTitle(Builder $query, ?string $search = null): Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where('title', 'LIKE', '%' . $search . '%');
    }

    /**
     * Scope: filter by date range.
     *
     * @param  string|null  $from  ISO/date string (inclusive)
     * @param  string|null  $to    ISO/date string (inclusive)
     */
    public function scopeFilterByDate(Builder $query, ?string $from = null, ?string $to = null): Builder
    {
        if ($from) {
            $fromDate = Carbon::parse($from)->startOfDay();
            $query->where('date', '>=', $fromDate);
        }

        if ($to) {
            $toDate = Carbon::parse($to)->endOfDay();
            $query->where('date', '<=', $toDate);
        }

        return $query;
    }
}


