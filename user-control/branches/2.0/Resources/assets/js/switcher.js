/* global tify */
'use strict';

import jQuery from 'jquery';
import 'presstify-framework/field/select-js/index';

jQuery(document).ready(function ($) {
    // Récupération de la liste des utilisateurs liés
    $(document).on('select-js:change', '.UserControlSwitcher-select--role', function () {
        // Bypass
        if (!$(this).val()) {
            return;
        }

        let $closest = $(this).closest('[aria-control="user_control-switcher"]'),
            $users = $('.UserControlSwitcher-select--user', $closest),
            o = $.parseJSON(decodeURIComponent($closest.data('options')));

        o.id = $('input[name="id"]', $closest).val();
        o.role = $(this).val();

        // Désactivation du champs de selection des utilisateurs durant la requête de récupération des éléments
        $users.tifySelectJs('disable');

        $.post(tify.ajax_url, o)
            .done(function (resp) {
                $users.before(resp).tifySelectJs('destroy');
                $('.UserControlSwitcher-select--user').tifySelectJs();
            });
    });

    // Soumission automatique du formulaire à l'issue de la selction d'un utilisateur
    $(document).on('select-js:change', '.UserControlSwitcher-select--user', function () {
        if ($(this).val() > 0) {
            $(this).closest('form').submit();
        }
    });
});