<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change valid_from and valid_until from DATE to DATETIME
     * Safe for both fresh and existing installations
     */
    public function up(): void
    {
        // Check if columns are already DATETIME
        $columns = DB::select("SHOW COLUMNS FROM discounts WHERE Field IN ('start_date', 'end_date')");
        
        $needsUpdate = false;
        foreach ($columns as $column) {
            if (stripos($column->Type, 'date') !== false && stripos($column->Type, 'datetime') === false) {
                $needsUpdate = true;
                break;
            }
        }
        
        // Only run if columns are still DATE type
        if ($needsUpdate) {
            // Backup existing data
            $discounts = DB::table('discounts')->get();
            
            // Drop and recreate columns
            Schema::table('discounts', function (Blueprint $table) {
                $table->dropColumn(['start_date', 'end_date']);
            });
            
            Schema::table('discounts', function (Blueprint $table) {
                $table->dateTime('start_date')->nullable()->after('target_type');
                $table->dateTime('end_date')->nullable()->after('start_date');
            });
            
            // Restore data with time appended
            foreach ($discounts as $discount) {
                DB::table('discounts')
                    ->where('id', $discount->id)
                    ->update([
                        'start_date' => $discount->start_date ? $discount->start_date . ' 00:00:00' : null,
                        'end_date' => $discount->end_date ? $discount->end_date . ' 23:59:59' : null,
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Backup existing data
        $discounts = DB::table('discounts')->get();
        
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });
        
        Schema::table('discounts', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('target_type');
            $table->date('end_date')->nullable()->after('start_date');
        });
        
        // Restore data (extract date only)
        foreach ($discounts as $discount) {
            DB::table('discounts')
                ->where('id', $discount->id)
                ->update([
                    'start_date' => $discount->start_date ? substr($discount->start_date, 0, 10) : null,
                    'end_date' => $discount->end_date ? substr($discount->end_date, 0, 10) : null,
                ]);
        }
    }
};
