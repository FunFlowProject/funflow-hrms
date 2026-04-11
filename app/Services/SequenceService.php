<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Sequence;
use Illuminate\Support\Facades\DB;

class SequenceService
{
    /**
     * Get and increment the next value for a given sequence name.
     * Starts at $initialValue if the sequence does not exist.
     *
     * @param string $name
     * @param int $initialValue
     * @return int
     */
    public static function nextValue(string $name, int $initialValue = 1000): int
    {
        return DB::transaction(function () use ($name, $initialValue) {
            $sequence = Sequence::query()
                ->where('name', $name)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = Sequence::query()->create([
                    'name' => $name,
                    'current_value' => $initialValue,
                ]);

                return (int) $sequence->current_value;
            }

            $newValue = (int) $sequence->current_value + 1;
            $sequence->update(['current_value' => $newValue]);

            return $newValue;
        });
    }
}
