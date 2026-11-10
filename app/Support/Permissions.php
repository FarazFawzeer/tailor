<?php

namespace App\Support;

/**
 * Single list of every permission in the system, grouped by module.
 * Permission names are "{module}.{action}" (ex: customers.create).
 *
 * After adding a module/action here, run:
 *   php artisan db:seed --class=RolesAndPermissionsSeeder
 */
class Permissions
{
    public const SUPER_ADMIN = 'super_admin';

    private const CRUD = [
        'view'   => 'View',
        'create' => 'Create',
        'edit'   => 'Edit',
        'delete' => 'Delete',
    ];

    /**
     * module => ['label' => ..., 'section' => ..., 'actions' => [action => label]]
     */
    public static function modules(): array
    {
        return [
            // System setup
            'dashboard' => ['label' => 'Production Dashboard', 'section' => 'System Setup', 'actions' => ['view' => 'View']],
            'users' => ['label' => 'Users', 'section' => 'System Setup', 'actions' => self::CRUD],
            'roles' => ['label' => 'Roles & Permissions', 'section' => 'System Setup', 'actions' => ['manage' => 'Manage']],
            'customers' => ['label' => 'Customers', 'section' => 'System Setup', 'actions' => self::CRUD],
            'staff' => ['label' => 'Staff', 'section' => 'System Setup', 'actions' => self::CRUD],
            'dress_types' => ['label' => 'Dress Types', 'section' => 'System Setup', 'actions' => self::CRUD],
            'measurement_templates' => ['label' => 'Measurement Templates', 'section' => 'System Setup', 'actions' => self::CRUD],
            'workflow_stages' => ['label' => 'Workflow Stages', 'section' => 'System Setup', 'actions' => self::CRUD],

            // Tailoring
            'jobs' => ['label' => 'Jobs', 'section' => 'Tailoring', 'actions' => self::CRUD],
            'measurements' => ['label' => 'Measurements', 'section' => 'Tailoring', 'actions' => ['view' => 'View', 'edit' => 'Enter / Edit']],
            'handover' => ['label' => 'Handover', 'section' => 'Tailoring', 'actions' => ['view' => 'View', 'create' => 'Hand Over', 'complete' => 'Mark Completed']],
            'workqueue' => ['label' => 'Work Queue', 'section' => 'Tailoring', 'actions' => ['view' => 'View Own Stage', 'all_stages' => 'View All Stages']],
            'delivery' => ['label' => 'Delivery & Invoice', 'section' => 'Tailoring', 'actions' => ['view' => 'View', 'manage' => 'Update Prices / Deliver']],
            'tailoring_reports' => ['label' => 'Tailoring Reports', 'section' => 'Tailoring', 'actions' => ['view' => 'View']],

            // Hiring
            'hire_items' => ['label' => 'Hire Items', 'section' => 'Hiring', 'actions' => self::CRUD],
            'hire_agreements' => ['label' => 'Hire Agreements', 'section' => 'Hiring', 'actions' => self::CRUD + ['return' => 'Return Items']],
            'hiring_availability' => ['label' => 'Availability', 'section' => 'Hiring', 'actions' => ['view' => 'View']],
            'hiring_reports' => ['label' => 'Hiring Reports', 'section' => 'Hiring', 'actions' => ['view' => 'View']],
        ];
    }

    /** Flat list of all permission names */
    public static function all(): array
    {
        $names = [];
        foreach (self::modules() as $module => $def) {
            foreach (array_keys($def['actions']) as $action) {
                $names[] = "{$module}.{$action}";
            }
        }

        return $names;
    }

    /** Modules grouped by section, for the role permission screen */
    public static function bySection(): array
    {
        $out = [];
        foreach (self::modules() as $module => $def) {
            $out[$def['section']][$module] = $def;
        }

        return $out;
    }

    /**
     * Starting permissions for the built-in roles (matches how access worked before
     * permissions existed). Only applied to a role that has no permissions yet.
     */
    public static function defaultRoleGrants(): array
    {
        $everything = self::all();

        $frontDesk = array_values(array_filter($everything, fn($p) => str_starts_with($p, 'dashboard.')
            || str_starts_with($p, 'customers.')
            || str_starts_with($p, 'jobs.')
            || str_starts_with($p, 'measurements.')
            || str_starts_with($p, 'handover.')
            || str_starts_with($p, 'workqueue.')
            || str_starts_with($p, 'delivery.')
            || str_starts_with($p, 'tailoring_reports.')
            || str_starts_with($p, 'hire_items.')
            || str_starts_with($p, 'hire_agreements.')
            || str_starts_with($p, 'hiring_availability.')
            || str_starts_with($p, 'hiring_reports.')));

        $stageWorker = ['workqueue.view', 'handover.view', 'handover.create'];

        return [
            self::SUPER_ADMIN => $everything,
            'admin' => array_values(array_diff($everything, ['roles.manage'])),
            'front_desk' => $frontDesk,
            'cutter' => $stageWorker,
            'sewing' => $stageWorker,
            'button' => $stageWorker,
            'ironing' => $stageWorker,
            'packaging' => $stageWorker,
        ];
    }

    /** "front_desk" -> "Front Desk" */
    public static function roleLabel(string $role): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $role));
    }

    /**
     * Pages a user may land on after login, in order of preference.
     * route name => permission needed
     */
    public static function homeCandidates(): array
    {
        return [
            'production.dashboard' => 'dashboard.view',
            'tailoring.workqueue.index' => 'workqueue.view',
            'tailoring.jobs.index' => 'jobs.view',
            'customers.index' => 'customers.view',
            'hiring.agreements.index' => 'hire_agreements.view',
            'hiring.items.index' => 'hire_items.view',
            'tailoring.delivery.index' => 'delivery.view',
            'tailoring.handover.index' => 'handover.view',
            'staff.index' => 'staff.view',
            'admin.users.index' => 'users.view',
            'admin.roles.index' => 'roles.manage',
        ];
    }
}
