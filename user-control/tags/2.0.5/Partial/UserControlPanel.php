<?php

/**
 * @name UserControl - Panneau.
 * @desc Interface utilisateur de bascule.
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Plugins\UserControl\Partial;

class UserControlPanel extends AbstractUserControlPartialItem
{
    /**
     * Liste des attributs de configuration.
     * @var array $attrs {
     *      @var string $take_over_id Identifiant de qualification du contrôleur d'affichage (requis).
     *      @var bool $in_footer Affichage automatique dans le pied de page du site.
     * }
     */
    protected $attributes = [
        'name'          => '',
        'in_footer'     => true
    ];

    /**
     * Indicateur de visibilité du controleur.
     * @var boolean
     */
    protected $visible = false;

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        add_action(
            'init',
            function () {
                wp_register_style(
                    'UserControlPanel',
                    $this->resourcesUrl('/assets/css/panel.css'),
                    [],
                    171218
                );

                wp_register_script(
                    'UserControlPanel',
                    $this->resourcesUrl('/assets/js/panel.js'),
                    ['UserControlSwitcher'],
                    171218,
                    true
                );
            }
        );

    }

    /**
     * {@inheritdoc}
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('UserControlPanel');
        wp_enqueue_script('UserControlPanel');
    }

    /**
     * {@inheritdoc}
     */
    public function display()
    {
        if ($this->visible) :
            if ($this->get('in_footer')) :
                add_action(
                    !is_admin() ? 'wp_footer' : 'admin_footer',
                    function () {
                        echo $this->viewer('panel', $this->all());
                    });
            else :
                return $this->viewer('panel', $this->all());
            endif;
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($attrs = [])
    {
        parent::parse($attrs);

        if (!$handler = $this->uc()->get($this->get('name'))) :
            return;
        elseif (!$handler->isAuth('switch') && !$handler->isAuth('restore')) :
            return;
        endif;

        $this->visible = true;

        $this->set('attrs.aria-control', 'user_control-panel');

        $this->set('attrs.aria-opened', 'false');

        //$this->set('switcher.role.attrs.id', 'UserControlPanel-switcher--role');
        //$this->set('switcher.role.picker.appendTo', '#UserControlPanel-switcher--role');
        //$this->set('switcher.user.attrs.id', 'UserControlPanel-switcher--user');
        //$this->set('switcher.user.picker.appendTo', '#UserControlPanel-switcher--user');

        if($handler->isAuth('switch')) :
            $this->set('auth', 'switch');
        elseif($handler->isAuth('restore')) :
            $this->set('auth', 'restore');
        endif;
    }
}