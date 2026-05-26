-- ============================================
-- StoreHub Migration: Payment Method Tracking
-- Run once in phpMyAdmin or MySQL CLI
-- ============================================

-- Step 1: Add payment_method column to orders
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS payment_method ENUM('cod', 'esewa') NOT NULL DEFAULT 'cod'
    AFTER status;

-- Step 2: Add esewa_ref column to orders (stores eSewa transaction reference)
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS esewa_ref VARCHAR(100) DEFAULT NULL
    AFTER payment_method;

-- Step 3: Backfill existing eSewa gateway orders (notes contain "eSewa Ref:")
UPDATE orders
SET
    payment_method = 'esewa',
    esewa_ref = TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(notes, 'eSewa Ref: ', -1), ' |', 1))
WHERE notes LIKE '%eSewa Ref:%'
  AND payment_method = 'cod';

-- Step 4: Backfill existing simulated eSewa orders (notes contain "simulated eSewa")
UPDATE orders
SET payment_method = 'esewa'
WHERE notes LIKE '%simulated eSewa%'
  AND payment_method = 'cod';
