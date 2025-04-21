document.addEventListener('DOMContentLoaded', function() {
    // 水平全選（按列）
    document.querySelectorAll('.select-all').forEach(function(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            let menuSlug = this.getAttribute('data-menu');
            let checkboxes = document.querySelectorAll('input.menu-permission[data-menu="' + menuSlug + '"]');
            checkboxes.forEach(function(checkbox) {
                if (!checkbox.disabled) {
                    checkbox.checked = selectAllCheckbox.checked;
                }
            });
        });
    });

    // 垂直全選（按欄）
    document.querySelectorAll('.select-column').forEach(function(selectColumnCheckbox) {
        selectColumnCheckbox.addEventListener('change', function() {
            let roleKey = this.getAttribute('data-role');
            let checkboxes = document.querySelectorAll('input.menu-permission[data-role="' + roleKey + '"]');
            checkboxes.forEach(function(checkbox) {
                if (!checkbox.disabled) {
                    checkbox.checked = selectColumnCheckbox.checked;
                }
            });
        });
    });

    // 單個核取方塊變更時更新全選狀態（水平和垂直）
    document.querySelectorAll('.menu-permission').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            let menuSlug = this.getAttribute('data-menu');
            let roleKey = this.getAttribute('data-role');

            // 更新水平全選狀態
            let rowCheckboxes = document.querySelectorAll('input.menu-permission[data-menu="' + menuSlug + '"]');
            let selectAllCheckbox = document.querySelector('input.select-all[data-menu="' + menuSlug + '"]');
            if (selectAllCheckbox) {
                let allRowChecked = Array.from(rowCheckboxes).every(cb => cb.checked || cb.disabled);
                selectAllCheckbox.checked = allRowChecked;
            }

            // 更新垂直全選狀態
            let columnCheckboxes = document.querySelectorAll('input.menu-permission[data-role="' + roleKey + '"]');
            let selectColumnCheckbox = document.querySelector('input.select-column[data-role="' + roleKey + '"]');
            if (selectColumnCheckbox) {
                let allColumnChecked = Array.from(columnCheckboxes).every(cb => cb.checked || cb.disabled);
                selectColumnCheckbox.checked = allColumnChecked;
            }
        });
    });

    // 拖放排序功能
    const sortable = new Sortable(document.getElementById('menu-sortable'), {
        handle: 'td',
        animation: 150,
        onEnd: function(evt) {
            let orderedMenuSlugs = [];
            const rows = document.querySelectorAll('#menu-sortable tr');
            rows.forEach(row => {
                orderedMenuSlugs.push(row.getAttribute('data-menu'));
            });

            // 使用 jQuery.ajax 發送請求
            jQuery.ajax({
                url: rbameAjax.ajaxUrl, // 使用內聯定義的 AJAX URL
                method: 'POST',
                data: {
                    action: 'update_menu_order',
                    menu_order: orderedMenuSlugs,
                    _ajax_nonce: rbameAjax.nonce // 使用內聯定義的 nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('菜單順序已更新！');
                    } else {
                        alert('更新失敗：' + response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert('請求失敗：' + error);
                }
            });
        }
    });
});