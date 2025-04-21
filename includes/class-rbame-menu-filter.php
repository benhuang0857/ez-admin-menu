<?php
if (!defined('ABSPATH')) {
    exit;
}

class RBAME_Menu_Filter {
    private $core;

    public function __construct(RBAME_Core $core) {
        $this->core = $core;
    }

    public function filter_admin_menu() {
        $user = wp_get_current_user();
        if (empty($user->roles)) {
            return;
        }

        $role = $user->roles[0];
        $permissions = $this->core->get_role_permissions();

        $admin_permissions = [];
        foreach ($permissions as $menu_slug => $roles) {
            if (in_array('administrator', $roles, true)) {
                $admin_permissions[] = $menu_slug;
            }
        }

        global $menu;
        foreach ($menu as $index => $item) {
            if (empty($item[2])) {
                continue;
            }

            $menu_slug = $item[2];
            if (!in_array($menu_slug, $admin_permissions) && $role !== 'administrator') {
                remove_menu_page($menu_slug);
                continue;
            }

            if (!in_array($role, $permissions[$menu_slug] ?? []) && $role !== 'administrator') {
                remove_menu_page($menu_slug);
            }
        }
    }
}