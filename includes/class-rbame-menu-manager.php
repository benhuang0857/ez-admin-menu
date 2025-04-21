<?php
if (!defined('ABSPATH')) {
    exit;
}

class RBAME_Menu_Manager {
    private $core;

    public function __construct(RBAME_Core $core) {
        $this->core = $core;
    }

    public function add_menu_manager_menu() {
        add_submenu_page(
            'role-menu-editor',
            '自訂選單管理',
            '自訂選單管理',
            'administrator',
            'menu-manager',
            [$this, 'render_menu_manager_page']
        );
    }
    
    public function register_custom_menu_settings() {
        register_setting('custom_menu_group', 'custom_menus');
    }

    public function render_menu_manager_page() {
        $menus = get_option('custom_menus', []);
        if (!is_array($menus)) $menus = [];
        ?>
        <div class="wrap">
            <h1>自訂選單管理</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('custom_menu_group');
                ?>
                <table id="custom-menu-table" class="form-table">
                    <thead>
                        <tr>
                            <th>選單標題</th>
                            <th>連結 URL</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $index => $menu): ?>
                            <tr>
                                <td><input type="text" name="custom_menus[<?php echo $index; ?>][title]" value="<?php echo esc_attr($menu['title'] ?? ''); ?>" class="regular-text" /></td>
                                <td><input type="url" name="custom_menus[<?php echo $index; ?>][url]" value="<?php echo esc_attr($menu['url'] ?? ''); ?>" class="regular-text" /></td>
                                <td><button type="button" class="button remove-row">刪除</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
    
                <p>
                    <button type="button" class="button" id="add-menu-row">新增一列</button>
                </p>
    
                <?php submit_button(); ?>
            </form>
        </div>
    
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tableBody = document.querySelector('#custom-menu-table tbody');
                const addRowButton = document.getElementById('add-menu-row');

                function reindexMenuRows() {
                    const rows = tableBody.querySelectorAll('tr');
                    rows.forEach((row, i) => {
                        const titleInput = row.querySelector('input[type="text"]');
                        const urlInput = row.querySelector('input[type="url"]');
                        titleInput.name = `custom_menus[${i}][title]`;
                        urlInput.name = `custom_menus[${i}][url]`;
                    });
                }

                addRowButton.addEventListener('click', function () {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td><input type="text" class="regular-text" /></td>
                        <td><input type="url" class="regular-text" /></td>
                        <td><button type="button" class="button remove-row">刪除</button></td>
                    `;
                    tableBody.appendChild(row);
                    reindexMenuRows();
                });

                tableBody.addEventListener('click', function (e) {
                    if (e.target.classList.contains('remove-row')) {
                        e.target.closest('tr').remove();
                        reindexMenuRows();
                    }
                });

                reindexMenuRows(); // 初始化也執行一次
            });
        </script>
        <?php
    }

    public function add_dynamic_custom_menus() {
        $menus = get_option('custom_menus', []);
        if (!is_array($menus)) return;
    
        foreach ($menus as $index => $menu) {
            if (empty($menu['title']) || empty($menu['url'])) continue;
    
            $slug = 'custom-menu-' . $index;
    
            // 先加一個假的頁面（href 指向 admin.php?page=xxx）
            add_menu_page(
                esc_html($menu['title']),
                esc_html($menu['title']),
                'manage_options',
                $slug,
                '__return_null', // 不顯示任何內容，因為不會進來
                'dashicons-email',
                90 + $index
            );
    
            // 用 JS 在 admin 頁面載入時修改 sidebar menu 的 href
            add_action('admin_head', function () use ($slug, $menu) {
                ?>
                <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const link = document.querySelector('#adminmenu a[href$="page=<?php echo esc_js($slug); ?>"]');
                    if (link) {
                        link.setAttribute('href', '<?php echo esc_js($menu['url']); ?>');
                     
                    }
                });
                </script>
                <?php
            });
        }
    }       
    
}