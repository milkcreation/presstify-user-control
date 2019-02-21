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
     * @internal requis. Tous les noms de qualification de services à traiter doivent être renseignés.
     * @var string[]
     */
    protected $provides = [
        'user-control',
        'user-control.handler'
    ];

    /**
     * @inheritdoc
     */
    public function boot()
    {
        add_action('after_setup_tify', function () {
            $partials = [
                'user-control.panel'     => UserControlPanel::class,
                'user-control.switcher'  => UserControlSwitcher::class,
                'user-control.trigger'   => UserControlTrigger::class,
            ];
            foreach ($partials as $name => $concrete) :
                partial()->register($name, $concrete);
            endforeach;
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
