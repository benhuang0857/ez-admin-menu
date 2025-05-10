<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="wrap">
    <h1>編輯角色權限</h1>

    <?php if (isset($_GET['updated'])) : ?>
        <div class="updated notice is-dismissible"><p>權限已更新！</p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('rbame_save_permissions'); ?>
        <input type="hidden" name="action" value="rbame_save_permissions">

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>選單名稱</th>
                    <?php if ($current_role === 'administrator') : ?>
                        <th>不可修改</th>
                        <th>全選（列）</th>
                    <?php endif; ?>
                    <?php foreach ($roles as $role_key => $role_info) : ?>
                        <th>
                            <?php echo esc_html($role_info['name']); ?><br>
                            <?php if ($current_role === 'administrator') : ?>
                                <input type="checkbox" class="select-column" data-role="<?php echo esc_attr($role_key); ?>">
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody id="menu-sortable">
                <?php foreach ($menus as $menu_slug => $menu_name) : ?>
                    <?php 
                        $is_all_checked = true;
                        foreach ($roles as $role_key => $role_info) {
                            if (!isset($permissions[$menu_slug]) || !in_array($role_key, $permissions[$menu_slug])) {
                                $is_all_checked = false;
                                break;
                            }
                        }
                        $is_editable = isset($editables[$menu_slug]) && $editables[$menu_slug] === true;
                    ?>
                    <?php if ($current_role === 'administrator' || in_array('administrator', $permissions[$menu_slug] ?? [])) : ?>
                        <tr data-menu="<?php echo esc_attr($menu_slug); ?>">
                            <td><?php echo esc_html($menu_name); ?></td>
                            <?php if ($current_role === 'administrator') : ?>
                                <td>
                                    <input type="checkbox" class="editable-checkbox"
                                        data-menu="<?php echo esc_attr($menu_slug); ?>"
                                        name="permissions[<?php echo esc_attr($menu_slug); ?>][editable]"
                                        value="1" <?php echo $is_editable ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <input type="checkbox" class="select-all" data-menu="<?php echo esc_attr($menu_slug); ?>" 
                                        <?php echo $is_all_checked ? 'checked' : ''; ?>>
                                </td>
                            <?php endif; ?>
                            <?php foreach ($roles as $role_key => $role_info) : ?>
                                <td>
                                    <input type="checkbox" class="menu-permission"
                                        data-menu="<?php echo esc_attr($menu_slug); ?>"
                                        data-role="<?php echo esc_attr($role_key); ?>"
                                        name="permissions[<?php echo esc_attr($menu_slug); ?>][<?php echo esc_attr($role_key); ?>]"
                                        value="1" 
                                        <?php echo (isset($permissions[$menu_slug]) && in_array($role_key, $permissions[$menu_slug])) ? 'checked' : ''; ?>
                                        <?php echo ($current_role !== 'administrator' && $is_editable) ? 'disabled' : ''; ?>>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary" name="rbame_save">儲存變更</button>
        </p>
    </form>
</div>

<!-- 添加內聯腳本 -->
<script type="text/javascript">
    var rbameAjax = {
        ajaxUrl: '<?php echo admin_url("admin-ajax.php"); ?>',
        nonce: '<?php echo wp_create_nonce("rbame_menu_order_nonce"); ?>'
    };
</script>

<?php
// wp_enqueue_script('sortable-js', 'https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js', [], '1.0', true);
wp_enqueue_script('rbame-admin-js', plugin_dir_url(__FILE__) . '../assets/js/rbame-admin.js', [], '1.0', true);
wp_enqueue_style('rbame-admin-css', plugin_dir_url(__FILE__) . '../assets/css/rbame-admin.css', [], '1.0');