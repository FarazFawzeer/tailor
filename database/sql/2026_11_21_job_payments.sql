-- Advance payments, balance settlement and pending bills
-- Same as: php artisan migrate  +  php artisan db:seed --class=RolesAndPermissionsSeeder
-- Safe to run more than once.
--
-- Run this BEFORE uploading the new code.
-- (If 2026_11_10_roles_permissions.sql was never run on live, run that one first.)

START TRANSACTION;

-- 1) Payments table
CREATE TABLE IF NOT EXISTS `job_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `job_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_type` varchar(20) NOT NULL DEFAULT 'payment',
  `method` varchar(20) NOT NULL DEFAULT 'cash',
  `paid_at` datetime NOT NULL,
  `received_by` bigint unsigned DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_payments_job_id_paid_at_index` (`job_id`, `paid_at`),
  KEY `job_payments_received_by_foreign` (`received_by`),
  CONSTRAINT `job_payments_job_id_foreign` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- mark the Laravel migration as done, so "php artisan migrate" won't try to create it again
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_21_100000_create_job_payments_table', t.next_batch
FROM (SELECT COALESCE(MAX(`batch`), 0) + 1 AS next_batch FROM `migrations`) t
WHERE NOT EXISTS (SELECT 1 FROM `migrations` WHERE `migration` = '2026_11_21_100000_create_job_payments_table');

-- 2) New permissions
INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
  ('payments.view', 'web', NOW(), NOW()),
  ('payments.create', 'web', NOW(), NOW()),
  ('payments.delete', 'web', NOW(), NOW());

-- 3) Give them to Super Admin (always), Admin and Front Desk
--    (Admin / Front Desk only if they don't have any payments permission yet,
--     so changes made later on the Roles & Permissions screen are kept)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p JOIN `roles` r ON r.name = 'super_admin' AND r.guard_name = 'web'
WHERE p.guard_name = 'web' AND p.name IN ('payments.view', 'payments.create', 'payments.delete');

INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p JOIN `roles` r ON r.name IN ('admin', 'front_desk') AND r.guard_name = 'web'
WHERE p.guard_name = 'web' AND p.name IN ('payments.view', 'payments.create', 'payments.delete')
  AND NOT EXISTS (
    SELECT 1 FROM `role_has_permissions` x JOIN `permissions` xp ON xp.id = x.permission_id
    WHERE x.role_id = r.id AND xp.name LIKE 'payments.%'
  );

COMMIT;

-- 4) Clear the cached permission list
-- Only works if the live .env has CACHE_STORE=database (the Laravel default).
-- With CACHE_STORE=file, empty the storage/framework/cache/data folder instead.
DELETE FROM `cache` WHERE `key` LIKE '%spatie.permission.cache%';
