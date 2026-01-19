# 🕐 Discount DateTime & Auto-Disable Implementation

## ✅ Implemented Features

### 1. Auto-Disable Expired Discounts ✅
**Command**: `discounts:disable-expired`
**Schedule**: Every minute
**Function**: Automatically disable discounts when `valid_until` time has passed

### 2. DateTime Support (Planned) ⏳
**Current**: Date only (valid_from, valid_until)
**Target**: DateTime (with hour and minute precision)

---

## 📁 Files Created

### 1. Command - Auto Disable
```
app/Console/Commands/DisableExpiredDiscounts.php
```
- Checks for active discounts where `valid_until < now()`
- Sets `is_active = false` for expired discounts
- Runs automatically every minute via scheduler

### 2. Schedule Configuration
```
routes/console.php
```
- Added: `Schedule::command('discounts:disable-expired')->everyMinute()`

### 3. Migration (Pending)
```
database/migrations/2026_01_18_140716_update_discounts_table_datetime.php
```
- Changes `valid_from` and `valid_until` from DATE to DATETIME
- **Status**: Not yet migrated (requires manual run)

---

## 🚀 How to Use

### Manual Command Test:
```bash
# Test the command manually
php artisan discounts:disable-expired

# Expected output:
✓ Disabled: Flash Sale 50% (expired at 2026-01-17 23:59:00)
✓ Disabled: Weekend Promo (expired at 2026-01-18 12:00:00)
Successfully disabled 2 expired discount(s).
```

### Start Scheduler:
```bash
# Start Laravel scheduler (development)
php artisan schedule:work

# Production (add to crontab):
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 Current Workaround (Before Migration)

Since DATE columns don't support time, here's the current behavior:

**valid_from**: `2026-01-18` → Active from `2026-01-18 00:00:00`  
**valid_until**: `2026-01-20` → Active until `2026-01-20 23:59:59`

Auto-disable will check at end of day (`23:59:59`).

---

## 📅 DateTime Implementation Plan

### Step 1: Run Migration (Manual)
```bash
# WARNING: This will modify discount table structure
php artisan migrate

# If error, manually alter table:
ALTER TABLE discounts 
MODIFY valid_from DATETIME NULL,
MODIFY valid_until DATETIME NULL;
```

### Step 2: Update Frontend (Datetime Picker)

**Current Form Input**:
```html
<input type="date" name="start_date" />
<input type="date" name="end_date" />
```

**Target**:
```html
<input type="datetime-local" name="start_date" />
<input type="datetime-local" name="end_date" />
```

### Step 3: Update Validation
```php
// DiscountController validation
'start_date' => 'nullable|date',        // Before
'start_date' => 'nullable|date_format:Y-m-d\TH:i', // After

'end_date' => 'nullable|date',          // Before  
'end_date' => 'nullable|date_format:Y-m-d\TH:i',   // After
```

---

## 🧪 Testing Scenarios

### Scenario 1: Create Discount with Datetime
```
Name: Flash Sale 24hr
Type: Percentage, 50%
Start: 2026-01-19 10:00
End: 2026-01-19 22:00

Expected:
✅ Active dari jam 10 pagi
✅ Auto-disable jam 10 malam keesokan hari
```

### Scenario 2: Expired Discount
```
Name: Weekend Promo
End: 2026-01-18 12:00

Action: Run php artisan discounts:disable-expired at 12:01

Expected:
✅ Discount set to is_active = false
✅ No longer applied at kasir
```

### Scenario 3: Scheduler Running
```
Terminal: php artisan schedule:work

Expected every minute:
[2026-01-18 14:30:00] Running scheduled command: discounts:disable-expired
[2026-01-18 14:31:00] Running scheduled command: discounts:disable-expired
...
```

---

## ⚙️ Configuration

### Scheduler Frequency Options:
```php
// routes/console.php

// Every minute (current)
Schedule::command('discounts:disable-expired')->everyMinute();

// Every 5 minutes (recommended for production)
Schedule::command('discounts:disable-expired')->everyFiveMinutes();

// Every hour
Schedule::command('discounts:disable-expired')->hourly();

// At specific time
Schedule::command('discounts:disable-expired')->dailyAt('00:00');
```

### Custom Timezone:
```php
Schedule::command('discounts:disable-expired')
    ->everyMinute()
    ->timezone('Asia/Jakarta');
```

---

## 🐛 Troubleshooting

### Scheduler Not Running?
```bash
# Check if scheduler is working
php artisan schedule:list

# Expected output:
0 * * * * * discounts:disable-expired .... Next Due: 1 minute from now
```

### Discounts Not Auto-Disabling?
```bash
# Check expired discounts manually
php artisan tinker

Discount::where('is_active', true)
    ->where('valid_until', '<', now())
    ->get();

# Manually disable them
php artisan discounts:disable-expired
```

### Migration Errors?
```
Error: Cannot change column type

Solution: Manually run SQL:
ALTER TABLE discounts 
MODIFY COLUMN valid_from DATETIME NULL,
MODIFY COLUMN valid_until DATETIME NULL;
```

---

## 📊 Database Schema

### Current (DATE):
```sql
valid_from  DATE NULL  -- Example: 2026-01-18
valid_until DATE NULL  -- Example: 2026-01-20
```

### After Migration (DATETIME):
```sql
valid_from  DATETIME NULL  -- Example: 2026-01-18 10:00:00
valid_until DATETIME NULL  -- Example: 2026-01-20 22:00:00
```

---

## ✅ Quick Start Checklist

- [x] Command created (`DisableExpiredDiscounts`)
- [x] Scheduler configured (`routes/console.php`)
- [ ] Migration run (manual approval needed)
- [ ] Scheduler started (`php artisan schedule:work`)
- [ ] Frontend updated to datetime-local input
- [ ] Validation updated for datetime format

---

## 🎯 Next Steps

1. **Test Command**: `php artisan discounts:disable-expired`
2. **Start Scheduler**: `php artisan schedule:work` (in separate terminal)
3. **Run Migration** (when ready): `php artisan migrate`
4. **Update Frontend**: Change input type to `datetime-local`

**Status**: ✅ Auto-disable ready, ⏳ DateTime inputs pending
