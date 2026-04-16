<?php

declare(strict_types=1);

namespace App\Events\Profit;

use App\Models\ProfitTransaction;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfitDistributed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly User $employee,
        public readonly ProfitTransaction $transaction,
        public readonly User $admin,
    ) {}
}
