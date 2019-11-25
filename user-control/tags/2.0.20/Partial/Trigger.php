<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Plugins\UserControl\Contracts\PartialTrigger;
use tiFy\Contracts\Partial\PartialFactory as BasePartialFactory;

class Trigger extends PartialFactory implements PartialTrigger
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
    public function display(): string
    {
        return $this->visible ? (string)$this->viewer('trigger', $this->all()) : '';
    }

    /**
     * @inheritDoc
     */
    public function parse(): BasePartialFactory
    {
        parent::parse();

        $action = $this->get('action');

        if (!$handler = $this->userControl->get($this->get('name'))) {
            return $this;
        } elseif (!$handler->isAuth($action)) {
            return $this;
        } else {
            $this->visible = true;

            $this->set('tag', 'a');

            switch ($action) {
                case 'switch' :
                    if (!$user = $handler->getUserData($this->get('user_id'))) {
                        return $this;
                    } elseif (!$handler->isAllowed($user)) {
                        return $this;
                    }

                    if (!$this->get('content')) {
                        $this->set('content', __('Naviguer comme', 'tify'));
                    }

                    if (!$this->get('attrs.title')) {
                        $this->set(
                            'attrs.title',
                            sprintf(__('Naviguer sur le site en tant que %s', 'tify'), $user->display_name)
                        );
                    }

                    $this->set('attrs.href', add_query_arg([
                        'action'           => $this->get('action'),
                        'user_id'          => $user->ID,
                        '_wp_http_referer' => $this->get('redirect_url'),
                    ], wp_nonce_url(home_url('/'), 'UserControl' . $this->get('name'), 'csrf-token')));
                    break;
                case 'restore' :
                    if (!$this->get('content')) {
                        $this->set('content', __('Rétablir', 'tify'));
                    }

                    if (!$this->get('attrs.title')) {
                        $this->set('attrs.title', __('Rétablissement de l\'utilisateur principal', 'tify'));
                    }

                    $this->set('attrs.href', add_query_arg([
                        'action'           => $this->get('action'),
                        '_wp_http_referer' => $this->get('redirect_url'),
                    ], wp_nonce_url(home_url('/'), 'UserControl' . $this->get('name'), 'csrf-token')));
                    break;
                default:
                    return $this;
                    break;
            }
        }

        return $this;
    }
}