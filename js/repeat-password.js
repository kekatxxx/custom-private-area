jQuery(document).ready(function() {
    jQuery('#registration-form').validate({
    rules : {
        reg_password : {
            minlength : 5
        },
        reg_password2 : {
            minlength : 5,
            equalTo : "#reg-pass"
        }
    }
    });
});