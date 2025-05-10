<?php
if (!defined('ABSPATH')) {
    exit;
}

class EZADM_MENU_Permissions {
    private $core;

    public function __construct(EZADM_MENU_Core $core) {
        $this->core = $core;
    }

    public function save_role_permissions() {
        if (!current_user_can('manage_options')) {
            wp_die(__('你沒有權限存取此頁面'));
        }

        check_admin_referer('rbame_save_permissions');

        $current_user = wp_get_current_user();
        $current_role = !empty($current_user->roles) ? $current_user->roles[0] : '';
        $all_roles = $this->core->get_all_roles();
        $editable_roles = ($current_role === 'administrator')
            ? $all_roles
            : $this->core->filter_roles_by_hierarchy($all_roles, $current_role);

        $new_permissions = $_POST['permissions'] ?? [];

        // 讀取舊的權限設定（保留不能動的角色）
        $old_permissions = get_option('rbame_role_permissions', []);
        $permissions = [];
        $editables = [];

        //  把出現過的所有 menu_slug 都收集起來
        $all_menu_slugs = array_unique(array_merge(
            array_keys($old_permissions),
            array_keys($new_permissions)
        ));

        foreach ($all_menu_slugs as $menu_slug) {
            $permissions[$menu_slug] = [];

            // 取得舊的這個 menu 的角色權限
            $old_roles = $old_permissions[$menu_slug] ?? [];
            $role_data = $new_permissions[$menu_slug] ?? [];

            if ($current_role === 'administrator') {
                // Admin 完全控制
                foreach ($all_roles as $role_key => $_) {
                    if (isset($role_data[$role_key]) && $role_data[$role_key] === '1') {
                        $permissions[$menu_slug][] = $role_key;
                    }
                }

                // 處理 editable 欄位
                if (isset($role_data['editable']) && $role_data['editable'] === '1') {
                    $editables[$menu_slug] = true;
                } else {
                    $editables[$menu_slug] = false;
                }

            } else {
                // Editor：保留不可編輯角色、修改自己可控的角色

                // Step 1: 保留不可編輯角色
                foreach ($old_roles as $role_key) {
                    if (!isset($editable_roles[$role_key])) {
                        $permissions[$menu_slug][] = $role_key;
                    }
                }

                // Step 2: 加入這次送出來的可編輯角色
                foreach ($editable_roles as $role_key => $_) {
                    if (isset($role_data[$role_key]) && $role_data[$role_key] === '1') {
                        $permissions[$menu_slug][] = $role_key;
                    }
                    // 沒勾就不加入，代表取消權限
                }
            }

            // 去除重複
            $permissions[$menu_slug] = array_unique($permissions[$menu_slug]);
        }

        // 儲存！
        update_option('rbame_role_permissions', $permissions);
        if ($current_role === 'administrator') {
            update_option('rbame_role_editables', $editables);
        }

        wp_redirect(admin_url('admin.php?page=role-menu-editor&updated=true'));
        exit;
    }




    // public function filter_roles_by_hierarchy($all_roles, $current_role) {
    //     return (new RBAME_Admin($this->core))->filter_roles_by_hierarchy($all_roles, $current_role);
    // }
}