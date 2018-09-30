<?php

namespace tiFy\Plugins\UserControl;

use tiFy\App\Container\AppServiceProvider;
use tiFy\Plugins\UserControl\Partial\ActionLink;
use tiFy\Plugins\UserControl\Partial\AdminBar;
use tiFy\Plugins\UserControl\Partial\SwitcherForm;

class UserControlServiceProvider extends AppServiceProvider
{
    /**
     * Liste des services à instance multiples auto-déclarés.
     * @var string[]
     */
    protected $bindings = [];

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

        $partials = [
            'user-control.action-link'   => ActionLink::class,
            'user-control.admin-bar'     => AdminBar::class,
            'user-control.switcher-form' => SwitcherForm::class,
        ];
        foreach ($partials as $name => $concrete) :
            $this->app
                ->resolve(Partial::class)
                ->register($name, $concrete);
        endforeach;
    }
}
