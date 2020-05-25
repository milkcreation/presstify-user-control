<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Plugins\UserControl\Contracts\PartialPanel;
use tiFy\Contracts\Partial\PartialDriver as BasePartialDriver;

class Panel extends AbstractPartialDriver implements PartialPanel
{
    /**
     * Indicateur de visibilité du controleur.
     * @var boolean
     */
    protected $visible = false;

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        parent::boot();

        add_action('init', function () {
            wp_register_style(
                'UserControlPanel',
                $this->userControl->resourcesUrl('/assets/css/panel.css'),
                [],
                171218
            );
            wp_register_script(
                'UserControlPanel',
                $this->userControl->resourcesUrl('/assets/js/panel.js'),
                ['UserControlSwitcher'],
                171218,
                true
            );
        });
    }

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            'name'      => '',
            'in_footer' => true,
            'show'      => 'all'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        if ($this->visible) {
            if ($this->get('in_footer')) {
                add_action(!is_admin() ? 'wp_footer' : 'admin_footer', function () {
                    echo parent::render();
                });
            } else {
                return parent::render();
            }
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function parse(): BasePartialDriver
    {
        parent::parse();

        if (!$handler = $this->userControl->get($this->get('name'))) {
            return $this;
        } elseif (!$handler->isAuth('switch') && !$handler->isAuth('restore')) {
            return $this;
        }

        switch($this->get('show')) {
            default:
            case 'all' :
                $this->visible = true;
                break;
            case 'switch' :
                if ($handler->isAuth('switch')) {
                    $this->visible = true;
                } else {
                    return $this;
                }
                break;
            case 'restore' :
                if ($handler->isAuth('restore')) {
                    $this->visible = true;
                } else {
                    return $this;
                }
                break;
        }


        if (!$this->get('attrs.id')) {
            $this->set('attrs.id', 'UserControlPanel-' . $this->getIndex());
        }

        $this->set('attrs.class', 'UserControlPanel');

        $this->set('attrs.aria-control', 'user_control-panel');

        $this->set('attrs.aria-opened', 'false');

        if ($handler->isAuth('switch')) {
            $this->set('auth', 'switch');
        } elseif ($handler->isAuth('restore')) {
            $this->set('auth', 'restore');
        }

        return $this;
    }
}