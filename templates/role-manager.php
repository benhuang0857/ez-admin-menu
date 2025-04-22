<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="wrap">
    <h1>角色管理</h1>

    <?php if (isset($_GET['updated'])) : ?>
        <div class="updated notice is-dismissible"><p>角色已更新！</p></div>
    <?php elseif (isset($_GET['deleted'])) : ?>
        <div class="updated notice is-dismissible"><p>角色已刪除！</p></div>
    <?php endif; ?>

    <h2>現有角色</h2>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th>角色名稱</th>
                <th>角色代號</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $role_key => $role_info) : ?>
                <tr>
                    <td><?php echo esc_html($role_info['name']); ?></td>
                    <td><?php echo esc_html($role_key); ?></td>
                    <td>
                        <a href="<?php echo admin_url('admin.php?page=role-manager&action=edit&role=' . esc_attr($role_key)); ?>" class="button">編輯</a>
                        <?php if ($role_key !== 'administrator') : ?>
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                <?php wp_nonce_field('rbame_delete_role'); ?>
                                <input type="hidden" name="action" value="rbame_delete_role">
                                <input type="hidden" name="role_key" value="<?php echo esc_attr($role_key); ?>">
                                <button type="submit" class="button" onclick="return confirm('確定要刪除此角色嗎？');">刪除</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>新增角色</h2>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('rbame_save_role'); ?>
        <input type="hidden" name="action" value="rbame_save_role">
        <table class="form-table">
            <tr>
                <th><label for="role_name">角色名稱</label></th>
                <td><input type="text" name="role_name" id="role_name" class="regular-text" required></td>
            </tr>
            <tr>
                <th><label for="role_key">角色代號</label></th>
                <td><input type="text" name="role_key" id="role_key" class="regular-text" placeholder="留空將使用角色名稱"></td>
            </tr>
            <tr>
                <th>權限（預設繼承 Editor）</th>
                <td>
                    <?php 
                    $current_user = wp_get_current_user();
                    $is_admin = in_array('administrator', $current_user->roles);
                    if ($is_admin) : ?>
                        <?php foreach ($default_capabilities as $cap => $value) : ?>
                            <label style="display: block;">
                                <input type="checkbox" name="capabilities[]" value="<?php echo esc_attr($cap); ?>" checked disabled>
                                <?php echo esc_html($cap); ?> (Editor 預設)
                            </label>
                        <?php endforeach; ?>
                        <p>額外權限（可選）：</p>
                        <?php 
                        $admin_capabilities = get_role('administrator')->capabilities;
                        $extra_capabilities = array_diff_key($admin_capabilities, $default_capabilities);
                        foreach ($extra_capabilities as $cap => $value) : ?>
                            <label style="display: block;">
                                <input type="checkbox" name="capabilities[]" value="<?php echo esc_attr($cap); ?>">
                                <?php echo esc_html($cap); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>只有管理員可以設定權限。新角色將預設繼承 Editor 權限。</p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="submit" class="button button-primary">新增角色</button>
        </p>
    </form>
</div>