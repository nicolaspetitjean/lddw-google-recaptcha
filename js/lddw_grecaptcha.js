function onloadCallback() {
    grecaptcha.render('lddw-grecaptcha', {
        'theme': LDDW_GR_CONFIG.theme,
        'sitekey': LDDW_GR_CONFIG.sitekey
    });
}

jQuery(function() {
    $('form .col-md-9 .form-group:last-child').after('<div class="form-group"><div id="lddw-grecaptcha"></div></div>');
});