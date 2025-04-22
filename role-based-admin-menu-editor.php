<?php
/**
 * Plugin Name: EZ Admin Menu
 * Description: 允許管理員自訂不同角色可見的 WordPress 管理選單
 * Version: 0.6
 * Author: Ben Huang, Erica Hsu
 */

if (!defined('ABSPATH')) {
    exit;
}

// 載入必要的檔案
require_once plugin_dir_path(__FILE__) . 'includes/class-rbame-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-rbame-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-rbame-permissions.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-rbame-menu-filter.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-rbame-role-manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-rbame-menu-manager.php';

function add_manage_options_to_editor() {
    $role = get_role('editor');
    if ($role && !$role->has_cap('manage_options')) {
        $role->add_cap('manage_options');
    }
}

// 初始化外掛
function rbame_init() {
    $core = new RBAME_Core();
    $admin = new RBAME_Admin($core);
    $permissions = new RBAME_Permissions($core);
    $menu_filter = new RBAME_Menu_Filter($core);
    $role_manager = new RBAME_Role_Manager($core);
    $menu_manager = new RBAME_Menu_Manager($core);


    add_action('admin_init', 'add_manage_options_to_editor');

    // 註冊動作
    add_action('admin_menu', [$admin, 'add_admin_menu']);
    add_action('admin_post_rbame_save_permissions', [$permissions, 'save_role_permissions']);
    add_action('admin_menu', [$menu_filter, 'filter_admin_menu'], 999);
    add_action('admin_menu', [$role_manager, 'add_role_manager_menu']);
    add_action('admin_menu', [$menu_manager, 'add_menu_manager_menu']);
    add_action('admin_menu', [$menu_manager, 'add_dynamic_custom_menus'], 99);
    add_action('admin_init', [$menu_manager, 'register_custom_menu_settings']);
    add_action('admin_post_rbame_save_role', [$role_manager, 'save_role']); // 處理角色儲存
    add_action('admin_post_rbame_delete_role', [$role_manager, 'delete_role']); // 處理角色刪除
}
add_action('plugins_loaded', 'rbame_init');