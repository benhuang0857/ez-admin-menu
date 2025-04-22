<?php
if (!defined('ABSPATH')) {
    exit;
}

class RBAME_Role_Manager {
    private $core;

    public function __construct(RBAME_Core $core) {
        $this->core = $core;
    }

    public function add_role_manager_menu() {
        add_submenu_page(
            'role-menu-editor',
            '角色管理',
            '角色管理',
            'administrator',
            'role-manager',
            [$this, 'render_role_manager_page']
        );
    }

    public function render_role_manager_page() {
        $roles = $this->core->get_all_roles();
        $editor_role = get_role('editor');
        $default_capabilities = $editor_role ? $editor_role->capabilities : [];

        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['role'])) {
            $role_key = sanitize_key($_GET['role']);
            $role = isset($roles[$role_key]) ? $roles[$role_key] : null;
            if ($role) {
                include plugin_dir_path(__FILE__) . '../templates/role-edit.php';
                return;
            }
        }

        include plugin_dir_path(__FILE__) . '../templates/role-manager.php';
    }

    public function save_role() {
        if (!current_user_can('manage_options')) {
            wp_die(__('你沒有權限執行此操作'));
        }

        check_admin_referer('rbame_save_role');

        $role_name = sanitize_text_field($_POST['role_name']);
        $role_key = sanitize_key($_POST['role_key'] ?: $role_name);
        $editor_role = get_role('editor');
        $default_capabilities = $editor_role ? $editor_role->capabilities : [];
        $capabilities = isset($_POST['capabilities']) && is_array($_POST['capabilities']) 
            ? array_merge($default_capabilities, array_fill_keys(array_map('sanitize_key', $_POST['capabilities']), true))
            : $default_capabilities;

        if (empty($role_name) || empty($role_key)) {
            wp_die(__('角色名稱和代號不能為空'));
        }

        $existing_role = get_role($role_key);
        if ($existing_role) {
            if ($role_key === 'administrator') {
                wp_die(__('無法編輯 Administrator 角色'));
            }

            // 強制更新角色名稱和權限
            global $wp_roles;
            if (!isset($wp_roles)) {
                $wp_roles = new WP_Roles();
            }

            // 更新角色名稱
            $wp_roles->roles[$role_key]['name'] = $role_name;
            // 更新權限
            foreach ($existing_role->capabilities as $cap => $value) {
                $existing_role->remove_cap($cap);
            }
            foreach ($capabilities as $cap => $value) {
                $existing_role->add_cap($cap);
            }
            // 儲存到資料庫
            update_option($wp_roles->role_key, $wp_roles->roles);
        } else {
            // 新增角色
            $result = add_role($role_key, $role_name, $capabilities);
            if (!$result) {
                wp_die(__('無法新增角色'));
            }
        }

        wp_redirect(admin_url('admin.php?page=role-manager&updated=true'));
        exit;
    }

    public function delete_role() {
        if (!current_user_can('manage_options')) {
            wp_die(__('你沒有權限執行此操作'));
        }

        check_admin_referer('rbame_delete_role');

        $role_key = sanitize_key($_POST['role_key']);
        if ($role_key === 'administrator') {
            wp_die(__('無法刪除 Administrator 角色'));
        }

        remove_role($role_key);
        wp_redirect(admin_url('admin.php?page=role-manager&deleted=true'));
        exit;
    }
}