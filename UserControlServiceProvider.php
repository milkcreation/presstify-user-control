<?php

namespace tiFy\Plugins\UserControl;

use tiFy\App\Container\AppServiceProvider;

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
        'user-control.partial.panel',
        'user-control.partial.switcher',
        'user-control.partial.trigger',
    ];

    /**
     * @inheritdoc
     */
    public function boot()
    {
        add_action('after_setup_theme', function () {
            foreach (['panel', 'switcher', 'trigger'] as $alias) {
                $classname = '\tiFy\Plugins\UserControl\Partial\UserControl' . ucfirst($alias);
                $this->getContainer()->get('partial')->register(
                    "user-control.{$alias}",
                    new $classname()
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

        $this->getContainer()->add('user-control.handler', function($name, $attrs = []) {
            return new UserControlItemHandler($name, $attrs);
        });
    }
}
