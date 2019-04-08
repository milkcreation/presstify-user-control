<?php

namespace tiFy\Plugins\UserControl\Partial;

class UserControlPanel extends AbstractUserControlPartialItem
{
    /**
     * Indicateur de visibilité du controleur.
     * @var boolean
     */
    protected $visible = false;

    /**
     * @inheritdoc
     */
    public function boot()
    {
        add_action('init', function () {
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
        });
    }

    /**
     * Liste des attributs de configuration.
     *
     * @return array $attrs {
     *      @var string $take_over_id Identifiant de qualification du contrôleur d'affichage (requis).
     *      @var bool $in_footer Affichage automatique dans le pied de page du site.
     * }
     */
    public function defaults()
    {
        return array_merge(parent::defaults(), [
            'name'          => '',
            'in_footer'     => true
        ]);
    }

    /**
     * @inheritdoc
     */
    public function display()
    {
        if ($this->visible) {
            if ($this->get('in_footer')) {
                add_action(!is_admin() ? 'wp_footer' : 'admin_footer', function () {
                    echo $this->viewer('panel', $this->all());
                });
            } else {
                return $this->viewer('panel', $this->all());
            }
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('UserControlPanel');
        wp_enqueue_script('UserControlPanel');
    }

    /**
     * @inheritdoc
     */
    public function parse()
    {
        parent::parse();

        if (!$handler = $this->uc()->get($this->get('name'))) {
            return;
        } elseif (!$handler->isAuth('switch') && !$handler->isAuth('restore')) {
            return;
        }

        $this->visible = true;

        if (!$this->get('attrs.id')) {
            $this->set('attrs.id', 'UserControlPanel-' . $this->getIndex());
        }

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