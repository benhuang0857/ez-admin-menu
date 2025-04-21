<?php
if (!defined('ABSPATH')) {
    exit;
}

class RBAME_Admin {
    private $core;

    public function __construct(RBAME_Core $core) {
        $this->core = $core;
        add_action('wp_ajax_update_menu_order', [$this, 'update_menu_order']);
        add_action('admin_menu', [$this, 'apply_menu_order'], 999); // 優先級設高一點，確保在選單生成後執行
    }

    public function add_admin_menu() {
        add_menu_page(
            '使用者權限設定',
            '使用者權限設定',
            'manage_options',
            'role-menu-editor',
            [$this, 'render_admin_page'],
            'dashicons-admin-generic',
            90
        );
    }

    public function render_admin_page() {
        $all_roles = $this->core->get_all_roles();
        $menus = $this->core->get_admin_menus();
        $permissions = $this->core->get_role_permissions();
        $editables = $this->core->get_role_editables();
    
        // 獲取保存的菜單順序
        $saved_order = get_option('rbame_menu_order', []);
        if (!empty($saved_order)) {
            $ordered_menus = [];
            // 按照保存的順序重新排序 $menus
            foreach ($saved_order as $slug) {
                if (isset($menus[$slug])) {
                    $ordered_menus[$slug] = $menus[$slug];
                    unset($menus[$slug]);
                }
            }
            // 將未排序的剩餘菜單加到末尾
            $menus = array_merge($ordered_menus, $menus);
        }
    
        $current_user = wp_get_current_user();
        $current_role = !empty($current_user->roles) ? $current_user->roles[0] : '';
        $roles = ($current_role === 'administrator') ? $all_roles : $this->core->filter_roles_by_hierarchy($all_roles, $current_role);
    
        include plugin_dir_path(__FILE__) . '../templates/admin-page.php';
    }

    private function filter_roles_by_hierarchy($all_roles, $current_role) {
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

    public function update_menu_order() {
        // 驗證 nonce
        if (!check_ajax_referer('rbame_menu_order_nonce', '_ajax_nonce', false)) {
            wp_send_json_error(['message' => '無效的請求！']);
        }
    
        // 檢查並獲取 menu_order
        if (isset($_POST['menu_order']) && is_array($_POST['menu_order'])) {
            $menu_order = array_map('sanitize_text_field', $_POST['menu_order']); // 安全過濾
            update_option('rbame_menu_order', $menu_order); // 保存到資料庫
            wp_send_json_success(['message' => '菜單順序已更新！']);
        } else {
            wp_send_json_error(['message' => '無效的菜單順序！']);
        }
    }

    // 新增方法：應用保存的順序到後台選單
    public function apply_menu_order() {
        global $menu;

        // 獲取插件保存的順序
        $saved_order = get_option('rbame_menu_order', []);
        if (empty($saved_order) || !is_array($saved_order)) {
            return; // 如果沒有保存的順序，則不做任何更改
        }

        // 獲取當前後台選單的 slug 與索引對應
        $menu_slugs = [];
        foreach ($menu as $index => $menu_item) {
            $slug = $menu_item[2]; // $menu_item[2] 是選單的 slug（如 'index.php'）
            $menu_slugs[$slug] = $index;
        }

        // 根據保存的順序重新排序 $menu
        $new_menu = [];
        $used_indices = [];

        // 先處理保存的順序中的選單項
        foreach ($saved_order as $slug) {
            if (isset($menu_slugs[$slug])) {
                $original_index = $menu_slugs[$slug];
                $new_menu[] = $menu[$original_index];
                $used_indices[] = $original_index;
            }
        }

        // 將未在保存順序中的選單項添加到末尾
        foreach ($menu as $index => $menu_item) {
            if (!in_array($index, $used_indices)) {
                $new_menu[] = $menu_item;
            }
        }

        // 更新全局 $menu
        $menu = $new_menu;
    }
}