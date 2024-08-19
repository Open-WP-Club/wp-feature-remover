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
});