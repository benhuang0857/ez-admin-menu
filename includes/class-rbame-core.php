<?php
if (!defined('ABSPATH')) {
    exit;
}

class RBAME_Core {
    public function get_all_roles() {
        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        return $wp_roles->roles;
    }

    public function get_admin_menus() {
        global $menu;
        $menu_items = [];
        foreach ($menu as $item) {
            if (!empty($item[0]) && !empty($item[2])) {
                $menu_items[$item[2]] = strip_tags($item[0]);
            }
        }
        return $menu_items;
    }

    public function get_role_permissions() {
        return get_option('rbame_role_permissions', []);
    }

    public function get_role_editables() {
        return get_option('rbame_role_editables', []);
    }

    public function filter_roles_by_hierarchy($all_roles, $current_role) {
        $editable_roles = get_editable_roles();
        $role_keys = array_keys($editable_roles);

        if ($current_role === 'administrator') {
            return $all_roles;
        }

        $current_role_index = array_search($current_role, $role_keys, true);
        if ($current_role_index === false) {
            return [];
        }

        $filtered_roles = [];
        for ($i = $current_role_index + 1; $i < count($role_keys); $i++) {
            $role_key = $role_keys[$i];
            if (isset($all_roles[$role_key])) {
                $filtered_roles[$role_key] = $all_roles[$role_key];
            }
        }
        return $filtered_roles;
    }
}