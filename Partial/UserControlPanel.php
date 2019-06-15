<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Contracts\Partial\PartialFactory;

class UserControlPanel extends AbstractUserControlPartialItem
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
     * @inheritDoc
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            'name'          => '',
            'in_footer'     => true
        ]);
    }

    /**
     * @inheritDoc
     */
    public function display(): string
    {
        if ($this->visible) {
            if ($this->get('in_footer')) {
                add_action(!is_admin() ? 'wp_footer' : 'admin_footer', function () {
                    echo $this->viewer('panel', $this->all());
                });
            } else {
                return (string)$this->viewer('panel', $this->all());
            }
        }
        return '';
    }

    /**
     * @inheritDoc
     */
    public function enqueue(): PartialFactory
    {
        wp_enqueue_style('UserControlPanel');
        wp_enqueue_script('UserControlPanel');

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function parse(): PartialFactory
    {
        parent::parse();

        if (!$handler = $this->uc()->get($this->get('name'))) {
            return $this;
        } elseif (!$handler->isAuth('switch') && !$handler->isAuth('restore')) {
            return $this;
        }

        $this->visible = true;

        if (!$this->get('attrs.id')) {
            $this->set('attrs.id', 'UserControlPanel-' . $this->getIndex());
        }

        $this->set('attrs.aria-control', 'user_control-panel');

        $this->set('attrs.aria-opened', 'false');

        if($handler->isAuth('switch')) {
            $this->set('auth', 'switch');
        } elseif($handler->isAuth('restore')) {
            $this->set('auth', 'restore');
        }

        return $this;
    }
}