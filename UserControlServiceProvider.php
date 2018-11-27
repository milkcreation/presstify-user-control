<?php

namespace tiFy\Plugins\UserControl;

use tiFy\App\Container\AppServiceProvider;
use tiFy\Plugins\UserControl\Partial\UserControlPanel;
use tiFy\Plugins\UserControl\Partial\UserControlSwitcher;
use tiFy\Plugins\UserControl\Partial\UserControlTrigger;

class UserControlServiceProvider extends AppServiceProvider
{
    /**
     * Liste des services à instance multiples auto-déclarés.
     * @var string[]
     */
    protected $bindings = [
        UserControlItemHandler::class,
    ];

    /**
     * Liste des services à instance unique auto-déclarés.
     * @var string[]
     */
    protected $singletons = [
        UserControl::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->app->resolve(UserControl::class);

        add_action(
            'after_setup_theme',
            function () {
                $partials = [
                    'user-control.panel'     => UserControlPanel::class,
                    'user-control.switcher'  => UserControlSwitcher::class,
                    'user-control.trigger'   => UserControlTrigger::class,
                ];
                foreach ($partials as $name => $concrete) :
                    $this->app
                        ->resolve('partial')
                        ->register($name, $concrete);
                endforeach;
            }
        );
    }
}
