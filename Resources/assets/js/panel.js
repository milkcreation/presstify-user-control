'use strict';

import jQuery from 'jquery';
import './switcher';

jQuery(document).ready(function ($) {
    /**
     * Ouverture/Fermeture.
     */
    $(document).on('click', '[aria-control="user_control-panel_toggle"]', function () {
        let $panel = $($(this).data('target')),
            visible = $panel.attr('aria-opened');

        if (visible === 'false') {
            $panel.attr('aria-opened', true);
        } else {
            $panel.attr('aria-opened', false);
        }
    });

    /**
     * Clic en dehors.
     * @todo EVOLUTION : Problème avec les listes de selection.
    $(document).on('click', function (event) {
        if (!$(event.target).closest('[aria-control="user_control-panel"][aria-opened="true"]').length) {
            $('[aria-control="user_control-panel"][aria-opened="true"]').attr('aria-opened', false);
        }
    });
     */
});