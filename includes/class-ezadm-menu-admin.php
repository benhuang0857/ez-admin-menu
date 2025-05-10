<?php
if (!defined('ABSPATH')) {
    exit;
}

class EZADM_MENU_Admin {
    private $core;

    public function __construct(EZADM_MENU_Core $core) {
        $this->core = $core;
        add_action('wp_ajax_update_menu_order', [$this, 'update_menu_order']);
        add_action('admin_menu', [$this, 'apply_menu_order'], 999);
        add_action('wp_ajax_save_menu_settings', [$this, 'save_menu_settings']);
        add_action('wp_ajax_render_add_menu_toggle', [$this, 'render_add_menu_toggle']);
        add_action('wp_ajax_delete_menu_item', [$this, 'delete_menu_item']);
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

        // 獲取保存的選單設置
        $menu_settings = get_option('rbame_menu_settings', []);

        // 添加新選單到 $menus
        if (!empty($menu_settings['new_menus'])) {
            foreach ($menu_settings['new_menus'] as $new_menu) {
                $menus[$new_menu['slug']] = !empty($new_menu['name']) ? $new_menu['name'] : $new_menu['slug'];
            }
        }

        // 獲取保存的選單順序
        $saved_order = get_option('rbame_menu_order', []);
        if (!empty($saved_order)) {
            $ordered_menus = [];
            foreach ($saved_order as $slug) {
                if (isset($menus[$slug])) {
                    $ordered_menus[$slug] = $menus[$slug];
                    unset($menus[$slug]);
                }
            }
            $menus = array_merge($ordered_menus, $menus);
        }

        $current_user = wp_get_current_user();
        $current_role = !empty($current_user->roles) ? $current_user->roles[0] : '';
        $roles = ($current_role === 'administrator') ? $all_roles : $this->filter_roles_by_hierarchy($all_roles, $current_role);

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
        if (!check_ajax_referer('rbame_menu_order_nonce', '_ajax_nonce', false)) {
            wp_send_json_error(['message' => '無效的請求！']);
        }

        if (isset($_POST['menu_order']) && is_array($_POST['menu_order'])) {
            $menu_order = array_map('sanitize_text_field', $_POST['menu_order']);
            update_option('rbame_menu_order', $menu_order);
            wp_send_json_success(['message' => '菜單順序已更新！']);
        } else {
            wp_send_json_error(['message' => '無效的菜單順序！']);
        }
    }

    public function apply_menu_order() {
        global $menu;

        $saved_order = get_option('rbame_menu_order', []);
        $menu_settings = get_option('rbame_menu_settings', []);

        if (empty($saved_order) || !is_array($saved_order)) {
            return;
        }

        $menu_slugs = [];
        foreach ($menu as $index => $menu_item) {
            $slug = $menu_item[2];
            $menu_slugs[$slug] = $index;
        }

        // 動態添加新選單
        if (!empty($menu_settings['new_menus'])) {
            foreach ($menu_settings['new_menus'] as $new_menu) {
                $slug = $new_menu['slug'];
                $name = !empty($new_menu['name']) ? $new_menu['name'] : $new_menu['slug'];
                $link = !empty($new_menu['link']) ? $new_menu['link'] : '#';
                $capability = 'manage_options';

                // 為新選單添加回調函數以處理自訂連結
                add_menu_page(
                    $name,
                    $name,
                    $capability,
                    $slug,
                    function () use ($link) {
                        // 如果連結有效，執行重定向
                        if ($link && $link !== '#') {
                            wp_redirect($link);
                            exit;
                        }
                        // 否則顯示空白頁面
                        echo '<div class="wrap"><h1>' . esc_html__('自訂選單', 'text-domain') . '</h1><p>' . esc_html__('無內容', 'text-domain') . '</p></div>';
                    },
                    'dashicons-admin-generic',
                    100
                );

                // 用 JS 在 admin 頁面載入時修改 sidebar menu 的 href
                add_action('admin_head', function () use ($slug, $link) {
                    ?>
                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const linkElement = document.querySelector('#adminmenu a[href$="page=<?php echo esc_js($slug); ?>"]');
                        if (linkElement) {
                            linkElement.setAttribute('href', '<?php echo esc_url($link); ?>');
                            console.log('Updated href to: <?php echo esc_url($link); ?>');
                        }
                    });
                    </script>
                    <?php
                });

                foreach ($menu as $index => $menu_item) {
                    if ($menu_item[2] === $slug) {
                        $menu_slugs[$slug] = $index;
                        break;
                    }
                }
            }
        }

        $new_menu = [];
        $used_indices = [];

        foreach ($saved_order as $slug) {
            if (isset($menu_slugs[$slug])) {
                $original_index = $menu_slugs[$slug];
                $menu_item = $menu[$original_index];

                // 更新選單名稱
                if (!empty($menu_settings['names'][$slug])) {
                    $menu_item[0] = $menu_settings['names'][$slug];
                    $menu_item[3] = $menu_settings['names'][$slug];
                }

                // 更新選單連結（對於現有選單）
                if (!empty($menu_settings['links'][$slug])) {
                    $menu_item[2] = $menu_settings['links'][$slug];
                }

                $new_menu[] = $menu_item;
                $used_indices[] = $original_index;
            }
        }

        foreach ($menu as $index => $menu_item) {
            if (!in_array($index, $used_indices)) {
                // 更新現有選單的連結
                if (!empty($menu_settings['links'][$menu_item[2]])) {
                    $menu_item[2] = $menu_settings['links'][$menu_item[2]];
                }
                $new_menu[] = $menu_item;
            }
        }

        if (!empty($menu_settings['separators'])) {
            foreach ($menu_settings['separators'] as $slug => $separator) {
                if ($separator) {
                    foreach ($new_menu as $index => $menu_item) {
                        if ($menu_item[2] === $slug) {
                            array_splice($new_menu, $index + 1, 0, [[
                                '',
                                'read',
                                'separator-' . $slug,
                                '',
                                'wp-menu-separator'
                            ]]);
                            break;
                        }
                    }
                }
            }
        }

        $menu = $new_menu;
    }

    public function save_menu_settings() {
        if (!check_ajax_referer('rbame_menu_order_nonce', '_ajax_nonce', false)) {
            wp_send_json_error(['message' => '無效的請求！']);
        }

        $menu_settings = isset($_POST['menu_settings']) ? $_POST['menu_settings'] : [];
        error_log('Received menu_settings: ' . print_r($menu_settings, true)); // 添加日誌

        $existing_settings = get_option('rbame_menu_settings', [
            'links' => [],
            'separators' => [],
            'names' => [],
            'new_menus' => []
        ]);

        $new_settings = [
            'links' => isset($menu_settings['links']) ? array_map('sanitize_text_field', $menu_settings['links']) : [],
            'separators' => isset($menu_settings['separators']) ? array_map('intval', $menu_settings['separators']) : [],
            'names' => isset($menu_settings['names']) ? array_map('sanitize_text_field', $menu_settings['names']) : [],
            'new_menus' => $existing_settings['new_menus']
        ];

        if (isset($menu_settings['new_menus']) && is_array($menu_settings['new_menus'])) {
            $existing_slugs = array_column($new_settings['new_menus'], 'slug');
            foreach ($menu_settings['new_menus'] as $new_menu) {
                $slug = sanitize_text_field($new_menu['slug']);
                $name = sanitize_text_field($new_menu['name']);
                $link = esc_url_raw($new_menu['link'] ?: '#');
                $separator = intval($new_menu['separator']);

                $index = array_search($slug, $existing_slugs);
                if ($index !== false) {
                    $new_settings['new_menus'][$index] = [
                        'slug' => $slug,
                        'name' => $name ?: $new_settings['new_menus'][$index]['name'],
                        'link' => $link,
                        'separator' => $separator
                    ];
                } else {
                    $new_settings['new_menus'][] = [
                        'slug' => $slug,
                        'name' => $name ?: '自訂選單',
                        'link' => $link,
                        'separator' => $separator
                    ];
                    $saved_order = get_option('rbame_menu_order', []);
                    if (!in_array($slug, $saved_order)) {
                        $saved_order[] = $slug;
                        update_option('rbame_menu_order', $saved_order);
                    }
                }
            }
        }

        $existing_settings['links'] = array_merge($existing_settings['links'], $new_settings['links']);
        $existing_settings['separators'] = array_merge($existing_settings['separators'], $new_settings['separators']);
        $existing_settings['names'] = array_merge($existing_settings['names'], $new_settings['names']);
        $existing_settings['new_menus'] = $new_settings['new_menus'];

        // error_log('Saving menu_settings: ' . print_r($existing_settings, true)); // 添加日誌
        // var_dump($existing_settings);
        // die();
        update_option('rbame_menu_settings', $existing_settings);
        wp_send_json_success(['message' => '選單設置已儲存']);
    }

    public function render_add_menu_toggle() {
        $unique_id = 'custom-menu-' . uniqid();
        $html = '
            <div class="accordion adminify_menu_item new-menu-item" id="wp-adminify-top-menu-' . esc_attr($unique_id) . '">
                <a data-menu="' . esc_attr($unique_id) . '" class="menu-editor-title accordion-button p-4 show" href="#">
                    <svg class="drag-icon is-pulled-left mr-2 ui-sortable-handle" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 14C13.1046 14 14 13.1046 14 12C14 10.8954 13.1046 10 12 10C10.8954 10 10 10.8954 10 12C10 13.1046 10.8954 14 12 14Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M12 7C13.1046 7 14 6.10457 14 5C14 3.89543 13.1046 3 12 3C10.8954 3 10 3.89543 10 5C10 6.10457 10.8954 7 12 7Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M12 21C13.1046 21 14 20.1046 14 19C14 17.8954 13.1046 17 12 17C10.8954 17 10 17.8954 10 19C10 20.1046 10.8954 21 12 21Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M5 14C6.10457 14 7 13.1046 7 12C7 10.8954 6.10457 10 5 10C3.89543 10 3 10.8954 3 12C3 13.1046 3.89543 14 5 14Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M5 7C6.10457 7 7 6.10457 7 5C7 3.89543 6.10457 3 5 3C3.89543 3 3 3.89543 3 5C3 6.10457 3.89543 7 5 7Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M5 21C6.10457 21 7 20.1046 7 19C7 17.8954 6.10457 17 5 17C3.89543 17 3 17.8954 3 19C3 20.1046 3.89543 21 5 21Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M19 14C20.1046 14 21 13.1046 21 12C21 10.8954 20.1046 10 19 10C17.8954 10 17 10.8954 17 12C17 13.1046 17.8954 14 19 14Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M19 7C20.1046 7 21 6.10457 21 5C21 3.89543 20.1046 3 19 3C17.8954 3 17 3.8954 17 5C17 6.10457 17.8954 7 19 7Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M19 21C20.1046 21 21 20.1046 21 19C21 17.8954 20.1046 17 19 17C17.8954 17 17 17.8954 17 19C17 20.1046 17.8954 21 19 21Z" fill="#4E4B66" fill-opacity="0.72"></path>
                    </svg>    
                    自訂選單
                </a>
                <div class="accordion-body adminify_top_level_settings">
                    <div class="tab-content panel p-4">
                        <div id="tab-' . esc_attr($unique_id) . '" class="tab-pane">
                            <div class="menu-editor-form">
                                <div class="columns">
                                    <div class="column">
                                        <label>選單名稱</label>
                                        <input class="menu_setting" type="text" name="name[' . esc_attr($unique_id) . ']" data-top-menu-id="' . esc_attr($unique_id) . '" placeholder="自訂選單" value="">
                                    </div>
                                    <div class="column">
                                        <label>選單連結</label>
                                        <input class="menu_setting" type="text" name="link[' . esc_attr($unique_id) . ']" data-top-menu-id="' . esc_attr($unique_id) . '" placeholder="輸入有效連結 (例如 /my-page 或 https://example.com)" value="">
                                    </div>
                                </div>
                                <div class="columns">
                                    <div class="column">
                                        <label><input class="menu_setting" name="separator[' . esc_attr($unique_id) . ']" type="checkbox"> 添加分隔線</label>
                                    </div>
                                </div>
                                <div class="columns">
                                    <div class="column">
                                        <button class="delete-menu-button button is-danger" data-top-menu-id="' . esc_attr($unique_id) . '">刪除選單</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';

        wp_send_json_success([
            'message' => '新選單已添加',
            'html' => $html
        ]);
    }

    public function delete_menu_item() {
        if (!check_ajax_referer('rbame_menu_order_nonce', '_ajax_nonce', false)) {
            wp_send_json_error(['message' => '無效的請求！']);
        }

        $menu_slug = isset($_POST['menu_slug']) ? sanitize_text_field($_POST['menu_slug']) : '';

        if (empty($menu_slug)) {
            wp_send_json_error(['message' => '無效的選單 slug！']);
        }

        $menu_settings = get_option('rbame_menu_settings', []);
        $saved_order = get_option('rbame_menu_order', []);

        // 從 new_menus 中移除
        if (!empty($menu_settings['new_menus'])) {
            $menu_settings['new_menus'] = array_filter($menu_settings['new_menus'], function ($menu) use ($menu_slug) {
                return $menu['slug'] !== $menu_slug;
            });
            $menu_settings['new_menus'] = array_values($menu_settings['new_menus']); // 重置索引
        }

        // 從選單順序中移除
        $saved_order = array_diff($saved_order, [$menu_slug]);

        // 從其他設置中移除
        if (isset($menu_settings['links'][$menu_slug])) {
            unset($menu_settings['links'][$menu_slug]);
        }
        if (isset($menu_settings['separators'][$menu_slug])) {
            unset($menu_settings['separators'][$menu_slug]);
        }
        if (isset($menu_settings['names'][$menu_slug])) {
            unset($menu_settings['names'][$menu_slug]);
        }

        // 更新資料庫
        update_option('rbame_menu_settings', $menu_settings);
        update_option('rbame_menu_order', $saved_order);

        wp_send_json_success(['message' => '選單已移除']);
    }
}