<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl;

use tiFy\Container\ServiceProvider;
use tiFy\Plugins\UserControl\{
    Partial\Panel as PartialPanel,
    Partial\Switcher as PartialSwitcher,
    Partial\Trigger as PartialTrigger
};
use tiFy\Support\Proxy\Partial;

class UserControlServiceProvider extends ServiceProvider
{
    /**
     * Liste des noms de qualification des services fournis.
     * {@internal Permet le chargement différé des services qualifié.}
     * @var string[]
     */
    protected $provides = [
        'user-control'
    ];

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        add_action('after_setup_theme', function () {
            $uc = $this->getContainer()->get('user-control');

            Partial::register('user-control-panel', (new PartialPanel())->setUserControl($uc));
            Partial::register('user-control-switcher', (new PartialSwitcher())->setUserControl($uc));
            Partial::register('user-control-trigger', (new PartialTrigger())->setUserControl($uc));
        });
    }

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        $this->getContainer()->share('user-control', function() {
            $concrete = new UserControl();

            foreach(config('user-control', []) as $name => $attrs) {
                $concrete->register($name, $attrs);
            }

            return $concrete;
        });
    }
}
