<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Discount;
use Carbon\Carbon;

class DisableExpiredDiscounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discounts:disable-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically disable expired discounts based on end_date datetime';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        
        // Find active discounts that have expired (end_date has passed)
        $expiredDiscounts = Discount::where('is_active', true)
            ->where('end_date', '<', $now)
            ->get();

        if ($expiredDiscounts->isEmpty()) {
            $this->info('No expired discounts found.');
            return 0;
        }

        $count = 0;
        foreach ($expiredDiscounts as $discount) {
            $discount->is_active = false;
            $discount->save();
            $count++;
            
            $this->line("✓ Disabled: {$discount->name} (expired at {$discount->end_date->format('Y-m-d H:i:s')})");
        }

        $this->info("Successfully disabled {$count} expired discount(s).");
        return 0;
    }
}
