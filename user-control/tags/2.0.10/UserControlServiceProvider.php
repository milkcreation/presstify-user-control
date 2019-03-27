<?php

namespace tiFy\Plugins\UserControl;

use tiFy\App\Container\AppServiceProvider;
use tiFy\Plugins\UserControl\Partial\UserControlPanel;
use tiFy\Plugins\UserControl\Partial\UserControlSwitcher;
use tiFy\Plugins\UserControl\Partial\UserControlTrigger;

class UserControlServiceProvider extends AppServiceProvider
{
    /**
     * Liste des noms de qualification des services fournis.
     * {@internal Permet le chargement différé des services qualifié.}
     * @var string[]
     */
    protected $provides = [
        'user-control',
        'user-control.handler',
        'partial.factory.user-control.panel',
        'partial.factory.user-control.switcher',
        'partial.factory.user-control.trigger',
    ];

    /**
     * @inheritdoc
     */
    public function boot()
    {
        add_action('after_setup_theme', function () {
            $this->getContainer()->get('user-control');

            foreach (['panel', 'switcher', 'trigger'] as $alias) {
                $this->getContainer()->get('partial')->register(
                    "user-control.{$alias}",
                    $this->getContainer()->get("partial.factory.user-control.{$alias}")
                );
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->getContainer()->share('user-control', function() {
            return new UserControl();
        });

        $this->getContainer()->add('user-control.handler', function($name, $attrs) {
            return new UserControlItemHandler($name, $attrs);
        });

        $this->getContainer()->add(
            'partial.factory.user-control.panel',
            function(?string $id = null, ?array $attrs = null) {
                return new UserControlPanel($id, $attrs);
        });

        $this->getContainer()->add(
            'partial.factory.user-control.switcher',
            function(?string $id = null, ?array $attrs = null) {
                return new UserControlSwitcher($id, $attrs);
        });

        $this->getContainer()->add(
            'partial.factory.user-control.trigger',
            function(?string $id = null, ?array $attrs = null) {
                return new UserControlTrigger($id, $attrs);
        });
    }
}
