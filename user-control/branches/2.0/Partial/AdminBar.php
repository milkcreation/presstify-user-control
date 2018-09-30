<?php

/**
 * @name UserControl - AdminBar.
 * @desc Controleur d'affichage d'une interface barre d'administration de bascule de compte utilisateur et de récupération de l'utilisateur principal.
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Partial\AbstractPartialItem;
use tiFy\Partial\Partial;
use tiFy\Plugins\UserControl\UserControl;

class AdminBar extends AbstractPartialItem
{
    /**
     * Liste des attributs de configuration.
     * @var array $attrs {
     *      @var string $take_over_id Identifiant de qualification du contrôleur d'affichage (requis).
     *      @var bool $in_footer Affichage automatique dans le pied de page du site.
     * }
     */
    protected $attributes = [
        'take_over_id'  => '',
        'in_footer'     => true
    ];

    /**
     * Initialisation globale de Wordpress.
     *
     * @return void
     */
    public function init()
    {
        \wp_register_style(
            'tify_control-take_over_admin_bar',
            $this->appAssetUrl('User/TakeOver/AdminBar/css/styles.css', get_class()),
            [],
            171218
        );
    }

    /**
     * Mise en file des scripts.
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        $this->appServiceGet(Partial::class)->enqueue('take_over_action_link');
        $this->appServiceGet(Partial::class)->enqueue('take_over_switcher_form');
        \wp_enqueue_style('tify_control-take_over_admin_bar');
    }

    /**
     * Affichage.
     *
     * @return string
     */
    protected function display()
    {
        if (!$this->appServiceGet(TakeOver::class)->get($this->get('take_over_id'))) :
            return;
        endif;

        $output  = "";
        $output .= "<div class=\"tiFyTakeOver-AdminBar\">";
        $output .= $this->appServiceGet(Partial::class)->display('TakeOverSwitcherForm', ['take_over_id' => $this->get('take_over_id')]);
        $output .= $this->appServiceGet(Partial::class)->display('TakeOverActionLink', ['take_over_id' => $this->get('take_over_id')]);
        $output .= "</div>";

        if ($in_footer) :
            $footer = function () use ($output) { echo $output; };
            \add_action((!is_admin() ? 'wp_footer' : 'admin_footer'), $footer);
        else :
            return $output;
        endif;
    }
}