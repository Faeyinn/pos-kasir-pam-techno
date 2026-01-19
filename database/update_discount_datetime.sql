-- Update existing discount data to include time
-- Add 00:00:00 to valid_from (start of day)
-- Add 23:59:59 to valid_until (end of day)

UPDATE discounts 
SET 
    valid_from = CASE 
        WHEN valid_from IS NOT NULL THEN CONCAT(DATE(valid_from), ' 00:00:00')
        ELSE NULL 
    END,
    valid_until = CASE 
        WHEN valid_until IS NOT NULL THEN CONCAT(DATE(valid_until), ' 23:59:59')
        ELSE NULL 
    END
WHERE valid_from IS NOT NULL OR valid_until IS NOT NULL;
