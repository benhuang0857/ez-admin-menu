<?php if (!defined('ABSPATH')) { exit; } ?>
<div class="wrap">
    <h1>編輯角色：<?php echo esc_html($role['name']); ?></h1>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('rbame_save_role'); ?>
        <input type="hidden" name="action" value="rbame_save_role">
        <input type="hidden" name="role_key" value="<?php echo esc_attr($role_key); ?>">
        <table class="form-table">
            <tr>
                <th><label for="role_name">角色名稱</label></th>
                <td><input type="text" name="role_name" id="role_name" value="<?php echo esc_attr($role['name']); ?>" class="regular-text" required></td>
            </tr>
            <tr>
                <th>權限（預設繼承 Editor）</th>
                <td>
                    <?php 
                    $current_user = wp_get_current_user();
                    $is_admin = in_array('administrator', $current_user->roles);
                    if ($is_admin) : ?>
                        <?php 
                        $editor_capabilities = get_role('editor')->capabilities;
                        foreach ($editor_capabilities as $cap => $value) : ?>
                            <label style="display: block;">
                                <input type="checkbox" name="capabilities[]" value="<?php echo esc_attr($cap); ?>" 
                                       checked disabled>
                                <?php echo esc_html($cap); ?> (Editor 預設)
                            </label>
                        <?php endforeach; ?>
                        <p>額外權限（可選）：</p>
                        <?php 
                        $admin_capabilities = get_role('administrator')->capabilities;
                        $extra_capabilities = array_diff_key($admin_capabilities, $editor_capabilities);
                        foreach ($extra_capabilities as $cap => $value) : ?>
                            <label style="display: block;">
                                <input type="checkbox" name="capabilities[]" value="<?php echo esc_attr($cap); ?>" 
                                       <?php echo isset($role['capabilities'][$cap]) && $role['capabilities'][$cap] ? 'checked' : ''; ?>>
                                <?php echo esc_html($cap); ?>
                            </label>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>只有管理員可以編輯權限。</p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="submit" class="button button-primary">更新角色</button>
            <a href="<?php echo admin_url('admin.php?page=role-manager'); ?>" class="button">返回</a>
        </p>
    </form>
</div>