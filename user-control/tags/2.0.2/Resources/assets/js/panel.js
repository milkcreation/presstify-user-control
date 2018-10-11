jQuery(document).ready(function ($) {
    /**
     * Ouverture/Fermeture.
     */
    $(document).on('click', '[aria-control="user_control-panel_toggle"]', function () {
        $panel = $($(this).data('target'));
        var visible = $panel.attr('aria-opened');

        if (visible === 'false') {
            $panel.attr('aria-opened', true);
        } else {
            $panel.attr('aria-opened', false);
        }
    });

    /**
     * Clic en dehors.
     * @todo problème avec les liste de selection.

    $(document).on('click', function (event) {
        if (!$(event.target).closest('[aria-control="user_control-panel"][aria-opened="true"]').length) {
            $('[aria-control="user_control-panel"][aria-opened="true"]').attr('aria-opened', false);
        }
    });
     */
});