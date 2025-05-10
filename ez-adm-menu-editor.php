<?php
/**
 * Plugin Name: EZ-Admin Menu Editor
 * Description: 允許管理員自訂不同角色可見的 WordPress 管理選單
 * Version: 0.6
 * Author: Erica, Ben
 */

if (!defined('ABSPATH')) exit;

// 載入必要的檔案
require_once plugin_dir_path(__FILE__) . 'includes/class-ezadm-menu-core.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ezadm-menu-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ezadm-menu-permissions.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ezadm-menu-filter.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ezadm-menu-role_manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ezadm-menu-menu_manager.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ezadm-menu-menu_editor.php';

add_action('plugins_loaded', function() {

    $core = new EZADM_MENU_Core();
    $admin = new EZADM_MENU_Admin($core);
    $permissions = new EZADM_MENU_Permissions($core);
    $menu_filter = new EZADM_MENU_Filter($core);
    $role_manager = new EZADM_MENU_Role_Manager($core);
    $menu_manager = new EZADM_MENU_Menu_Manager($core);
    $menu_editor = new EZADM_MENU_Menu_Editor($core);

    add_action('admin_menu', [$admin, 'add_admin_menu']);
    add_action('admin_post_rbame_save_permissions', [$permissions, 'save_role_permissions']);
    add_action('admin_menu', [$menu_filter, 'filter_admin_menu'], 999);
    add_action('admin_menu', [$role_manager, 'add_role_manager_menu']);
    add_action('admin_menu', [$menu_manager, 'add_menu_manager_menu']);
    add_action('admin_menu', [$menu_editor, 'add_admin_menu']);
    // add_action('admin_menu', [$menu_manager, 'add_dynamic_custom_menus'], 99);
    add_action('admin_init', [$menu_manager, 'register_custom_menu_settings']);
    add_action('admin_post_rbame_save_role', [$role_manager, 'save_role']); // 處理角色儲存
    add_action('admin_post_rbame_delete_role', [$role_manager, 'delete_role']); // 處理角色刪除
});