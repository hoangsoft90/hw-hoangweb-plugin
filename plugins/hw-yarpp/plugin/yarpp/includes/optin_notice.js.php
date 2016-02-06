<script type='text/javascript'>
    jQuery(function($){
        $(document.body).on('click', '#hw-yarpp-optin-button', function(){
            $(this).closest('p').find('.button').attr('disabled',true);
            $('#hw-yarpp-optin').attr('checked', true);
            $.ajax({
                type:'POST',
                url : ajaxurl,
                data: {
                    action: 'hw_yarpp_optin_<?php echo $optinAction?>',
                    '_ajax_nonce': $('#hw_yarpp_optin-nonce').val()
                },
                success: hw_yarppRedirectAdmin
            });
        });
    });

    function hw_yarppRedirectAdmin(resp){
        if (resp === 'ok'){
            window.location.href = 'options-general.php?page=hw-yarpp';
        }
    }
</script>