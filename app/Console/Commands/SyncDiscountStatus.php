<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Discount;
use Carbon\Carbon;

class SyncDiscountStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discounts:sync-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically activate/deactivate discounts based on start_date and end_date schedule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        
        // Find discounts that should be ACTIVATED (start_date reached, end_date not passed, currently inactive, auto_activate enabled)
        $toActivate = Discount::where('is_active', false)
            ->where('auto_activate', true)  // Only auto-activate if enabled
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get();

        $activatedCount = 0;
        foreach ($toActivate as $discount) {
            $discount->is_active = true;
            $discount->save();
            $activatedCount++;
            
            $this->line("✓ Activated: {$discount->name} (started at {$discount->start_date->format('Y-m-d H:i:s')})");
        }

        // Find discounts that should be DEACTIVATED (end_date has passed, currently active)
        $toDeactivate = Discount::where('is_active', true)
            ->where('end_date', '<', $now)
            ->get();

        $deactivatedCount = 0;
        foreach ($toDeactivate as $discount) {
            $discount->is_active = false;
            $discount->save();
            $deactivatedCount++;
            
            $this->line("✓ Deactivated: {$discount->name} (expired at {$discount->end_date->format('Y-m-d H:i:s')})");
        }

        if ($activatedCount === 0 && $deactivatedCount === 0) {
            $this->info('No discounts need status changes.');
        } else {
            $this->info("Successfully activated {$activatedCount} and deactivated {$deactivatedCount} discount(s).");
        }

        return 0;
    }
}
