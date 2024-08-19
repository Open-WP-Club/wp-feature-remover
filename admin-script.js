jQuery(document).ready(function($) {
    // Tab functionality
    $('.wp-feature-remover-tab-link').click(function(e) {
        e.preventDefault();
        var tabId = $(this).data('tab');
        
        $('.wp-feature-remover-tab-link').removeClass('active');
        $('.wp-feature-remover-tab-content').removeClass('active');
        
        $(this).addClass('active');
        $('#' + tabId).addClass('active');
    });

    // Activate the first tab by default
    $('.wp-feature-remover-tab-link:first').click();

    // Toggle all checkboxes in a category
    $('.toggle-category').change(function() {
        var category = $(this).data('category');
        var isChecked = $(this).prop('checked');
        $('#' + category + ' input[type="checkbox"]').not(this).prop('checked', isChecked);
    });

    // Update "Toggle All" checkbox when individual checkboxes change
    $('.wp-feature-remover-feature input[type="checkbox"]').change(function() {
        var category = $(this).closest('.wp-feature-remover-tab-content').attr('id');
        var totalCheckboxes = $('#' + category + ' .wp-feature-remover-feature input[type="checkbox"]').length;
        var checkedCheckboxes = $('#' + category + ' .wp-feature-remover-feature input[type="checkbox"]:checked').length;
        $('#' + category + ' .toggle-category').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Initialize "Toggle All" checkboxes
    $('.wp-feature-remover-tab-content').each(function() {
        var category = $(this).attr('id');
        var totalCheckboxes = $('#' + category + ' .wp-feature-remover-feature input[type="checkbox"]').length;
        var checkedCheckboxes = $('#' + category + ' .wp-feature-remover-feature input[type="checkbox"]:checked').length;
        $('#' + category + ' .toggle-category').prop('checked', totalCheckboxes === checkedCheckboxes);
    });
});