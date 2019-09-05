<?php

namespace tiFy\Plugins\UserControl;

use tiFy\Container\ServiceProvider;
use tiFy\Plugins\UserControl\Partial\UserControlPanel;
use tiFy\Plugins\UserControl\Partial\UserControlSwitcher;
use tiFy\Plugins\UserControl\Partial\UserControlTrigger;

class UserControlServiceProvider extends ServiceProvider
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
     * @inheritDoc
     */
    public function boot(): void
    {
        add_action('after_setup_theme', function () {
            $this->getContainer()->get('user-control');

            foreach (['panel', 'switcher', 'trigger'] as $alias) {
                $this->getContainer()->get('partial')->set(
                    "user-control-{$alias}",
                    $this->getContainer()->get("partial.factory.user-control.{$alias}")
                );
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share('user-control', function() {
            return new UserControl();
        });

        $this->getContainer()->add('user-control.handler', function($name, $attrs) {
            return new UserControlItemHandler($name, $attrs);
        });

        $this->getContainer()->add('partial.factory.user-control.panel', function() {
            return new UserControlPanel();
        });

        $this->getContainer()->add('partial.factory.user-control.switcher', function() {
                return new UserControlSwitcher();
        });

        $this->getContainer()->add('partial.factory.user-control.trigger', function() {
                return new UserControlTrigger();
        });
    }
}
