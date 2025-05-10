<?php
if (!defined('ABSPATH')) {
    exit;
}

class EZADM_MENU_Menu_Editor {
    private $core;

    public function __construct(EZADM_MENU_Core $core) {
        $this->core = $core;
    }

    public function add_admin_menu() {
        add_submenu_page(
            'role-menu-editor',
            '使用者側邊菜單設定',
            '使用者側邊菜單設定',
            'administrator',
            'admin-menu-editor',
            [$this, 'render_admin_page']
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

        include plugin_dir_path(__FILE__) . '../templates/menu-editor.php';
    }
}