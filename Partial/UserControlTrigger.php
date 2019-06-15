<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Contracts\Partial\PartialFactory;

class UserControlTrigger extends AbstractUserControlPartialItem
{
    /**
     * Indicateur de visibilité du controleur.
     * @var boolean
     */
    protected $visible = false;

    /**
     * @inheritdoc
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
            'viewer'       => []
        ]);
    }

    /**
     * @inheritdoc
     */
    public function display(): string
    {
        return $this->visible ? (string) $this->viewer('trigger', $this->all()) : '';
    }

    /**
     * @inheritdoc
     */
    public function parse(): PartialFactory
    {
        parent::parse();

        $action = $this->get('action');

        if (!$handler = $this->uc()->get($this->get('name'))) :
            return $this;
        elseif (!$handler->isAuth($action)) :
            return $this;
        else :
            $this->visible = true;

            $this->set('tag', 'a');

            switch ($action) :
                case 'switch' :
                    if (!$user = $handler->getUserData($user_id)) :
                        return $this;
                    elseif (!$handler->isAllowed($user->ID)) :
                        return $this;
                    endif;

                    if (!$this->get('content')) :
                        $this->set('content', __('Naviguer comme', 'tify'));
                    endif;

                    if (!$this->get('attrs.title')) :
                        $this->set('attrs.title',
                            sprintf(__('Naviguer sur le site en tant que %s', 'tify'), $user->display_name));
                    endif;

                    $this->set(
                        'attrs.href',
                        add_query_arg(
                            [
                                'action'  => $this->get('action'),
                                'user_id' => $user->ID,
                            ],
                            wp_nonce_url($this->get('redirect_url'), 'UserControl' . $this->get('name'), 'csrf-token')
                        )
                    );
                    break;
                case 'restore' :
                    if (!$this->get('content')) :
                        $this->set('content', __('Rétablir', 'tify'));
                    endif;

                    if (!$this->get('attrs.title')) :
                        $this->set('attrs.title', __('Rétablissement de l\'utilisateur principal', 'tify'));
                    endif;

                    $this->set(
                        'attrs.href',
                        add_query_arg(
                            [
                                'action' => $this->get('action'),
                            ],
                            wp_nonce_url($this->get('redirect_url'), 'UserControl' . $this->get('name'), 'csrf-token')
                        )
                    );
                    break;
                default:
                    return $this;
                    break;
            endswitch;
        endif;

        return $this;
    }
}