jQuery(document).ready(function($){
    // Récupération de la liste des utilisateurs liés
    $(document).on('tifyselectadd', '.UserControlSwitcher-select--role', function(e){
        // Bypass
        if(!$(this).val()) {
            return;
        }

        var $roles = $(this),
            $closest = $(this).closest('[aria-control="user_control-switcher"]');
        var $users =  $('.UserControlSwitcher-select--user', $closest),
            o = $.parseJSON(decodeURIComponent($closest.data('options')));
        o.id = $('input[name="id"]', $closest).val();
        o.role = $(this).val();

        // Désactivation du champs de selection des utilisateurs durant la requête de récupération des éléments
        $users.tifyselect('disable');

        $.post(
            tify_ajaxurl,
            o
        )
            .done(function(resp){
                $users.before(resp).tifyselect('destroy');
                $('.UserControlSwitcher-select--user').tifyselect();
            });
    });

    // Soumission automatique du formulaire à l'issue de la selction d'un utilisateur
    $(document).on('tifyselectadd', '.UserControlSwitcher-select--user', function(){
        if($(this).val() > 0) {
            $(this).closest('form').submit();
        }
    });
});