-- Roles & Permissions setup (same as: php artisan db:seed --class=RolesAndPermissionsSeeder)
-- Safe to run more than once: only adds what is missing.
-- Roles that already have permissions are NOT changed (except super_admin, which always gets all).

START TRANSACTION;

-- 1) Permissions
INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
  ('dashboard.view', 'web', NOW(), NOW()),
  ('users.view', 'web', NOW(), NOW()),
  ('users.create', 'web', NOW(), NOW()),
  ('users.edit', 'web', NOW(), NOW()),
  ('users.delete', 'web', NOW(), NOW()),
  ('roles.manage', 'web', NOW(), NOW()),
  ('customers.view', 'web', NOW(), NOW()),
  ('customers.create', 'web', NOW(), NOW()),
  ('customers.edit', 'web', NOW(), NOW()),
  ('customers.delete', 'web', NOW(), NOW()),
  ('staff.view', 'web', NOW(), NOW()),
  ('staff.create', 'web', NOW(), NOW()),
  ('staff.edit', 'web', NOW(), NOW()),
  ('staff.delete', 'web', NOW(), NOW()),
  ('dress_types.view', 'web', NOW(), NOW()),
  ('dress_types.create', 'web', NOW(), NOW()),
  ('dress_types.edit', 'web', NOW(), NOW()),
  ('dress_types.delete', 'web', NOW(), NOW()),
  ('measurement_templates.view', 'web', NOW(), NOW()),
  ('measurement_templates.create', 'web', NOW(), NOW()),
  ('measurement_templates.edit', 'web', NOW(), NOW()),
  ('measurement_templates.delete', 'web', NOW(), NOW()),
  ('workflow_stages.view', 'web', NOW(), NOW()),
  ('workflow_stages.create', 'web', NOW(), NOW()),
  ('workflow_stages.edit', 'web', NOW(), NOW()),
  ('workflow_stages.delete', 'web', NOW(), NOW()),
  ('jobs.view', 'web', NOW(), NOW()),
  ('jobs.create', 'web', NOW(), NOW()),
  ('jobs.edit', 'web', NOW(), NOW()),
  ('jobs.delete', 'web', NOW(), NOW()),
  ('measurements.view', 'web', NOW(), NOW()),
  ('measurements.edit', 'web', NOW(), NOW()),
  ('handover.view', 'web', NOW(), NOW()),
  ('handover.create', 'web', NOW(), NOW()),
  ('handover.complete', 'web', NOW(), NOW()),
  ('workqueue.view', 'web', NOW(), NOW()),
  ('workqueue.all_stages', 'web', NOW(), NOW()),
  ('delivery.view', 'web', NOW(), NOW()),
  ('delivery.manage', 'web', NOW(), NOW()),
  ('tailoring_reports.view', 'web', NOW(), NOW()),
  ('hire_items.view', 'web', NOW(), NOW()),
  ('hire_items.create', 'web', NOW(), NOW()),
  ('hire_items.edit', 'web', NOW(), NOW()),
  ('hire_items.delete', 'web', NOW(), NOW()),
  ('hire_agreements.view', 'web', NOW(), NOW()),
  ('hire_agreements.create', 'web', NOW(), NOW()),
  ('hire_agreements.edit', 'web', NOW(), NOW()),
  ('hire_agreements.delete', 'web', NOW(), NOW()),
  ('hire_agreements.return', 'web', NOW(), NOW()),
  ('hiring_availability.view', 'web', NOW(), NOW()),
  ('hiring_reports.view', 'web', NOW(), NOW());

-- 2) Built-in roles (created only if missing)
INSERT IGNORE INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
  ('super_admin', 'web', NOW(), NOW()),
  ('admin', 'web', NOW(), NOW()),
  ('front_desk', 'web', NOW(), NOW()),
  ('cutter', 'web', NOW(), NOW()),
  ('sewing', 'web', NOW(), NOW()),
  ('button', 'web', NOW(), NOW()),
  ('ironing', 'web', NOW(), NOW()),
  ('packaging', 'web', NOW(), NOW());

-- 3) Default permissions per role
-- super_admin: every permission
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p JOIN `roles` r ON r.name = 'super_admin' AND r.guard_name = 'web'
WHERE p.guard_name = 'web' AND p.name IN ('dashboard.view', 'users.view', 'users.create', 'users.edit', 'users.delete', 'roles.manage', 'customers.view', 'customers.create', 'customers.edit', 'customers.delete', 'staff.view', 'staff.create', 'staff.edit', 'staff.delete', 'dress_types.view', 'dress_types.create', 'dress_types.edit', 'dress_types.delete', 'measurement_templates.view', 'measurement_templates.create', 'measurement_templates.edit', 'measurement_templates.delete', 'workflow_stages.view', 'workflow_stages.create', 'workflow_stages.edit', 'workflow_stages.delete', 'jobs.view', 'jobs.create', 'jobs.edit', 'jobs.delete', 'measurements.view', 'measurements.edit', 'handover.view', 'handover.create', 'handover.complete', 'workqueue.view', 'workqueue.all_stages', 'delivery.view', 'delivery.manage', 'tailoring_reports.view', 'hire_items.view', 'hire_items.create', 'hire_items.edit', 'hire_items.delete', 'hire_agreements.view', 'hire_agreements.create', 'hire_agreements.edit', 'hire_agreements.delete', 'hire_agreements.return', 'hiring_availability.view', 'hiring_reports.view');

-- admin (only if this role has no permissions yet)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p JOIN `roles` r ON r.name = 'admin' AND r.guard_name = 'web'
WHERE p.guard_name = 'web' AND p.name IN ('dashboard.view', 'users.view', 'users.create', 'users.edit', 'users.delete', 'customers.view', 'customers.create', 'customers.edit', 'customers.delete', 'staff.view', 'staff.create', 'staff.edit', 'staff.delete', 'dress_types.view', 'dress_types.create', 'dress_types.edit', 'dress_types.delete', 'measurement_templates.view', 'measurement_templates.create', 'measurement_templates.edit', 'measurement_templates.delete', 'workflow_stages.view', 'workflow_stages.create', 'workflow_stages.edit', 'workflow_stages.delete', 'jobs.view', 'jobs.create', 'jobs.edit', 'jobs.delete', 'measurements.view', 'measurements.edit', 'handover.view', 'handover.create', 'handover.complete', 'workqueue.view', 'workqueue.all_stages', 'delivery.view', 'delivery.manage', 'tailoring_reports.view', 'hire_items.view', 'hire_items.create', 'hire_items.edit', 'hire_items.delete', 'hire_agreements.view', 'hire_agreements.create', 'hire_agreements.edit', 'hire_agreements.delete', 'hire_agreements.return', 'hiring_availability.view', 'hiring_reports.view')
  AND NOT EXISTS (SELECT 1 FROM `role_has_permissions` x WHERE x.role_id = r.id);

-- front_desk (only if this role has no permissions yet)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p JOIN `roles` r ON r.name = 'front_desk' AND r.guard_name = 'web'
WHERE p.guard_name = 'web' AND p.name IN ('dashboard.view', 'customers.view', 'customers.create', 'customers.edit', 'customers.delete', 'jobs.view', 'jobs.create', 'jobs.edit', 'jobs.delete', 'measurements.view', 'measurements.edit', 'handover.view', 'handover.create', 'handover.complete', 'workqueue.view', 'workqueue.all_stages', 'delivery.view', 'delivery.manage', 'tailoring_reports.view', 'hire_items.view', 'hire_items.create', 'hire_items.edit', 'hire_items.delete', 'hire_agreements.view', 'hire_agreements.create', 'hire_agreements.edit', 'hire_agreements.delete', 'hire_agreements.return', 'hiring_availability.view', 'hiring_reports.view')
  AND NOT EXISTS (SELECT 1 FROM `role_has_permissions` x WHERE x.role_id = r.id);

-- cutter (only if this role has no permissions yet)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p JOIN `roles` r ON r.name = 'cutter' AND r.guard_name = 'web'
WHERE p.guard_name = 'web' AND p.name IN ('workqueue.view', 'handover.view', 'handover.create')
  AND NOT EXISTS (SELECT 1 FROM `role_has_permissions` x WHERE x.role_id = r.id);

-- sewing (only if this role has no permissions yet)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p JOIN `roles` r ON r.name = 'sewing' AND r.guard_name = 'web'
WHERE p.guard_name = 'web' AND p.name IN ('workqueue.view', 'handover.view', 'handover.create')
  AND NOT EXISTS (SELECT 1 FROM `role_has_permissions` x WHERE x.role_id = r.id);

-- button (only if this role has no permissions yet)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p JOIN `roles` r ON r.name = 'button' AND r.guard_name = 'web'
WHERE p.guard_name = 'web' AND p.name IN ('workqueue.view', 'handover.view', 'handover.create')
  AND NOT EXISTS (SELECT 1 FROM `role_has_permissions` x WHERE x.role_id = r.id);

-- ironing (only if this role has no permissions yet)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p JOIN `roles` r ON r.name = 'ironing' AND r.guard_name = 'web'
WHERE p.guard_name = 'web' AND p.name IN ('workqueue.view', 'handover.view', 'handover.create')
  AND NOT EXISTS (SELECT 1 FROM `role_has_permissions` x WHERE x.role_id = r.id);

-- packaging (only if this role has no permissions yet)
INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
SELECT p.id, r.id FROM `permissions` p JOIN `roles` r ON r.name = 'packaging' AND r.guard_name = 'web'
WHERE p.guard_name = 'web' AND p.name IN ('workqueue.view', 'handover.view', 'handover.create')
  AND NOT EXISTS (SELECT 1 FROM `role_has_permissions` x WHERE x.role_id = r.id);

COMMIT;

-- 4) Clear the cached permission list
-- Only works if the live .env has CACHE_STORE=database (the Laravel default).
-- With CACHE_STORE=file, empty the storage/framework/cache/data folder instead.
DELETE FROM `cache` WHERE `key` LIKE '%spatie.permission.cache%';
