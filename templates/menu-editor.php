<?php if (!defined('ABSPATH')) { exit; } ?>
<style>
    .accordion {
        padding: 10px;
        margin-bottom: 10px;
    }

    .wp-adminify .wp-adminify--menu--editor--settings .accordion.add-new-menu-editor-item {
        background: var(--adminify-text-color);
        border: 2px dashed #4e4b66 !important;
        -webkit-border-radius: 6px;
        border-radius: 6px;
        -webkit-box-shadow: 0 0 54px 0 rgba(20, 20, 42, .07);
        box-shadow: 0 0 54px 0 rgba(20, 20, 42, .07);
        cursor: pointer;
        padding: 16px;
        text-align: center;
    }

    .delete-menu-button {
        margin-top: 10px;
        padding: 8px 16px;
        font-size: 14px;
        line-height: 1.5;
        border-radius: 4px;
    }
</style>
<div class="wrap">
    <div class="wp-adminify--menu--editor--container mt-4">
        
        <div class="wp-adminify--page--title--actions mt-1 is-pulled-right">
            <button class="page-title-action mr-3 adminify_menu_save_settings">儲存</button>
        </div>
        <h1>使用者側邊菜單設定</h1>
        <div class="wp-adminify--menu--editor--settings mt-5 pt-3 ui-sortable loaded" id="admin-menu-editor">
            <?php $i = 1; ?>
            <?php 
            // 獲取保存的選單設置
            $menu_settings = get_option('rbame_menu_settings', []);
            $new_menus = !empty($menu_settings['new_menus']) ? $menu_settings['new_menus'] : [];
            ?>
            <?php foreach ($menus as $menu_slug => $menu_name) : ?>
                <?php
                // 檢查是否為新選單，並獲取保存的設置
                $is_new_menu = in_array($menu_slug, array_column($new_menus, 'slug'));
                $saved_name = isset($menu_settings['names'][$menu_slug]) ? $menu_settings['names'][$menu_slug] : $menu_name;
                $saved_link = isset($menu_settings['links'][$menu_slug]) ? $menu_settings['links'][$menu_slug] : '';
                $saved_separator = isset($menu_settings['separators'][$menu_slug]) && $menu_settings['separators'][$menu_slug] ? 'checked' : '';
                
                if ($is_new_menu) {
                    foreach ($new_menus as $new_menu) {
                        if ($new_menu['slug'] === $menu_slug) {
                            $saved_name = $new_menu['name'];
                            $saved_link = $new_menu['link'];
                            $saved_separator = $new_menu['separator'] ? 'checked' : '';
                            break;
                        }
                    }
                }
                ?>
                <div class="accordion adminify_menu_item<?php echo $is_new_menu ? ' new-menu-item' : ''; ?>" id="wp-adminify-top-menu-<?php echo esc_attr($menu_slug); ?>">
                    <a data-menu="<?php echo esc_attr($menu_slug); ?>" class="menu-editor-title accordion-button p-4" href="#">
                        <svg class="drag-icon is-pulled-left mr-2 ui-sortable-handle" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 14C13.1046 14 14 13.1046 14 12C14 10.8954 13.1046 10 12 10C10.8954 10 10 10.8954 10 12C10 13.1046 10.8954 14 12 14Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M12 7C13.1046 7 14 6.10457 14 5C14 3.89543 13.1046 3 12 3C10.8954 3 10 3.89543 10 5C10 6.10457 10.8954 7 12 7Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M12 21C13.1046 21 14 20.1046 14 19C14 17.8954 13.1046 17 12 17C10.8954 17 10 17.8954 10 19C10 20.1046 10.8954 21 12 21Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M5 14C6.10457 14 7 13.1046 7 12C7 10.8954 6.10457 10 5 10C3.89543 10 3 10.8954 3 12C3 13.1046 3.89543 14 5 14Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M5 7C6.10457 7 7 6.10457 7 5C7 3.89543 6.10457 3 5 3C3.89543 3 3 3.89543 3 5C3 6.10457 3.89543 7 5 7Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M5 21C6.10457 21 7 20.1046 7 19C7 17.8954 6.10457 17 5 17C3.89543 17 3 17.8954 3 19C3 20.1046 3.89543 21 5 21Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M19 14C20.1046 14 21 13.1046 21 12C21 10.8954 20.1046 10 19 10C17.8954 10 17 10.8954 17 12C17 13.1046 17.8954 14 19 14Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M19 7C20.1046 7 21 6.10457 21 5C21 3.89543 20.1046 3 19 3C17.8954 3 17 3.89543 17 5C17 6.10457 17.8954 7 19 7Z" fill="#4E4B66" fill-opacity="0.72"></path>
                            <path d="M19 21C20.1046 21 21 20.1046 21 19C21 17.8954 20.1046 17 19 17C17.8954 17 17 17.8954 17 19C17 20.1046 17.8954 21 19 21Z" fill="#4E4B66" fill-opacity="0.72"></path>
                        </svg>
                        <?php echo esc_html($saved_name); ?>
                    </a>
                    <div class="accordion-body adminify_top_level_settings">
                        <div class="tab-content panel p-4">
                            <div id="tab-<?php echo esc_attr($menu_slug); ?>-<?php echo $i++; ?>" class="tab-pane">
                                <div class="menu-editor-form">
                                    <div class="columns">
                                        <div class="column">
                                            <label>重新命名為</label>
                                            <input class="menu_setting" type="text" name="name[<?php echo esc_attr($menu_slug); ?>]" data-top-menu-id="<?php echo esc_attr($menu_slug); ?>" placeholder="<?php echo esc_attr($menu_name); ?>" value="<?php echo esc_attr($saved_name); ?>">
                                        </div>
                                        <div class="column">
                                            <label>更改連結</label>
                                            <input class="menu_setting" type="text" name="link[<?php echo esc_attr($menu_slug); ?>]" data-top-menu-id="<?php echo esc_attr($menu_slug); ?>" placeholder="<?php echo esc_attr($menu_slug); ?>" value="<?php echo esc_attr($saved_link); ?>">
                                        </div>
                                    </div>
                                    <div class="columns">
                                        <div class="column">
                                            <label><input class="menu_setting" name="separator[<?php echo esc_attr($menu_slug); ?>]" type="checkbox" <?php echo $saved_separator; ?>> 添加分隔線</label>
                                        </div>
                                    </div>
                                    <div class="columns">
                                        <div class="column">
                                            <button class="delete-menu-button button is-danger" data-top-menu-id="<?php echo esc_attr($menu_slug); ?>">刪除選單</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="render-here"></div>

            <div class="accordion add-new-menu-editor-item">
                <div class="inner-text">
                    <i class="dashicons dashicons-plus-alt"></i>
                    <span class="title">添加新選單</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script type="text/javascript">
    var rbameAjax = {
        ajaxUrl: '<?php echo admin_url("admin-ajax.php"); ?>',
        nonce: '<?php echo wp_create_nonce("rbame_menu_order_nonce"); ?>'
    };
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const adminMenuEditor = document.getElementById('admin-menu-editor');
    if (!adminMenuEditor) {
        console.error('Element #admin-menu-editor not found in the DOM');
        return;
    }

    // 初始化 Sortable
    const sortable = new Sortable(adminMenuEditor, {
        handle: 'a',
        animation: 150,
        onEnd: function (evt) {
            let orderedMenuSlugs = [];
            const rows = document.querySelectorAll('#admin-menu-editor a');
            rows.forEach(row => {
                orderedMenuSlugs.push(row.getAttribute('data-menu'));
            });

            jQuery.ajax({
                url: rbameAjax.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'update_menu_order',
                    menu_order: orderedMenuSlugs,
                    _ajax_nonce: rbameAjax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        alert('菜單順序已更新！');
                    } else {
                        alert('更新失敗：' + response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    alert('請求失敗：' + error);
                }
            });
        }
    });

    // 儲存按鈕點擊事件
    jQuery('.adminify_menu_save_settings').on('click', function () {
        let menuSettings = {
            links: {},
            separators: {},
            names: {},
            new_menus: []
        };

        jQuery('.adminify_menu_item').each(function () {
            const menuSlug = jQuery(this).find('a').data('menu');
            const nameInput = jQuery(this).find('input[name="name[' + menuSlug + ']"]').val().trim();
            const linkInput = jQuery(this).find('input[name="link[' + menuSlug + ']"]').val().trim();
            const separatorChecked = jQuery(this).find('input[name="separator[' + menuSlug + ']"]').is(':checked');

            if (jQuery(this).hasClass('new-menu-item')) {
                menuSettings.new_menus.push({
                    slug: menuSlug,
                    name: nameInput || '自訂選單',
                    link: linkInput || '#',
                    separator: separatorChecked ? 1 : 0
                });
            } else {
                if (nameInput) menuSettings.names[menuSlug] = nameInput;
                if (linkInput) menuSettings.links[menuSlug] = linkInput;
                menuSettings.separators[menuSlug] = separatorChecked ? 1 : 0;
            }
        });

        console.log('Sending menuSettings:', menuSettings); // 添加日誌

        jQuery.ajax({
            url: rbameAjax.ajaxUrl,
            method: 'POST',
            data: {
                action: 'save_menu_settings',
                menu_settings: menuSettings,
                _ajax_nonce: rbameAjax.nonce
            },
            success: function (response) {
                console.log('Response:', response); // 添加日誌
                if (response.success) {
                    alert('選單設置已儲存！');
                    // location.reload();
                } else {
                    alert('儲存失敗：' + response.data.message);
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX Error:', error); // 添加日誌
                alert('請求失敗：' + error);
            }
        });
    });

    // 添加新選單項目
    jQuery('.add-new-menu-editor-item').on('click', function () {
        jQuery.ajax({
            url: rbameAjax.ajaxUrl,
            method: 'POST',
            data: {
                action: 'render_add_menu_toggle',
                _ajax_nonce: rbameAjax.nonce
            },
            success: function (response) {
                if (response.success) {
                    jQuery('.render-here').append(response.data.html);
                } else {
                    alert('添加失敗：' + response.data.message);
                }
            },
            error: function (xhr, status, error) {
                alert('請求失敗：' + error);
            }
        });
    });

    // 刪除選單項目
    jQuery(document).on('click', '.delete-menu-button', function (e) {
        e.preventDefault();
        const menuItem = jQuery(this).closest('.adminify_menu_item');
        if (confirm('確定要移除此選單嗎？')) {
            const menuSlug = jQuery(this).data('top-menu-id');
            menuItem.remove();
            jQuery.ajax({
                url: rbameAjax.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'delete_menu_item',
                    menu_slug: menuSlug,
                    _ajax_nonce: rbameAjax.nonce
                },
                success: function (response) {
                    if (response.success) {
                        alert('選單已移除！');
                    } else {
                        alert('移除失敗：' + response.data.message);
                    }
                },
                error: function (xhr, status, error) {
                    alert('請求失敗：' + error);
                }
            });
        }
    });
});
</script>