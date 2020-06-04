<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Plugins\UserControl\Contracts\PartialTrigger;
use tiFy\Contracts\Partial\PartialDriver as BasePartialDriver;
use tiFy\Support\Proxy\Url;

class Trigger extends AbstractPartialDriver implements PartialTrigger
{
    /**
     * Indicateur de visibilité du controleur.
     * @var boolean
     */
    protected $visible = false;

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            'name'         => '',
            'user_id'      => 0,
            'action'       => 'restore',
            'content'      => '',
            'attrs'        => [],
            'redirect_url' => home_url('/'),
            'viewer'       => [],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return $this->visible ? parent::render() : '';
    }

    /**
     * @inheritDoc
     */
    public function parse(): BasePartialDriver
    {
        parent::parse();

        $action = $this->get('action');

        if (!$handler = $this->userControl->get($this->get('name'))) {
            return $this;
        } elseif (!$handler->isAuth($action)) {
            return $this;
        } else {
            $this->set('trigger', [
                'attrs' => [
                    'class' => 'UserControlTrigger',
                ],
                'tag'   => 'a',
            ]);

            switch ($action) {
                case 'switch' :
                    if (!$user = $handler->getUserData($this->get('user_id'))) {
                        return $this;
                    } elseif (!$handler->isAllowed($user)) {
                        return $this;
                    }

                    $this->visible = true;

                    if (!$content = $this->get('content')) {
                        $this->set('trigger.content', __('Naviguer comme', 'tify'));
                    } else {
                        $this->set('trigger.content', $content);
                    }

                    if (!$title = $this->get('attrs.title')) {
                        $this->set(
                            'trigger.attrs.title',
                            sprintf(__('Naviguer sur le site en tant que %s', 'tify'), $user->display_name)
                        );
                    } else {
                        $this->set('trigger.attrs.title', $title);
                    }

                    $this->set('trigger.attrs.href', Url::set($handler->route->getUrl())->with([
                        'action'           => $this->get('action'),
                        'csrf-token'       => wp_create_nonce('UserControl' . $this->get('name')),
                        'user_id'          => $user->ID,
                        '_wp_http_referer' => $this->get('redirect_url'),
                    ])->render());
                    break;
                case 'restore' :
                    $this->visible = true;

                    if (!$content = $this->get('content')) {
                        $this->set('trigger.content', __('Rétablir', 'tify'));
                    } else {
                        $this->set('trigger.content', $content);
                    }

                    if (!$title = $this->get('attrs.title')) {
                        $this->set('trigger.attrs.title', __('Rétablissement de l\'utilisateur principal', 'tify'));
                    } else {
                        $this->set('trigger.attrs.title', $title);
                    }

                    $this->set('trigger.attrs.href', Url::set($handler->route->getUrl())->with([
                        'action'           => $this->get('action'),
                        'csrf-token'       => wp_create_nonce('UserControl' . $this->get('name')),
                        '_wp_http_referer' => $this->get('redirect_url'),
                    ])->render());
                    break;
                default:
                    return $this;
                    break;
            }
        }

        return $this;
    }
}