<?php

namespace tiFy\Plugins\UserControl\Partial;

class UserControlSwitcher extends AbstractUserControlPartialItem
{
    /**
     * Liste des attributs de configuration.
     * @var array $role {
     *              Attributs de configuration du champ de selection des roles.
     * @see \tiFy\Field\Fields\SelectJs\SelectJs
     *          }
     * @var array $user {
     *              Attributs de configuration du champ de selection des utilisateurs.
     * @see \tiFy\Field\Fields\SelectJs\SelectJs
     *          }
     * }
     */
    protected $attributes = [
        'name'   => '',
        'attrs'  => [],
        'role'   => [],
        'user'   => [],
        'viewer' => [],
    ];

    /**
     * Indicateur de visibilité du controleur.
     * @var boolean
     */
    protected $visible = false;

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        add_action(
            'init',
            function () {
                add_action(
                    'wp_ajax_user_control_switcher',
                    [$this, 'ajax']
                );

                add_action(
                    'wp_ajax_nopriv_user_control_switcher',
                    [$this, 'ajax']
                );

                wp_register_script(
                    'UserControlSwitcher',
                    $this->resourcesUrl('/assets/js/switcher.js'),
                    [],
                    171218,
                    true
                );
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function defaults()
    {
        return [
            'redirect_url' => home_url('/'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function enqueue_scripts()
    {
        field('user-control.switcher')->enqueue_scripts();
        wp_enqueue_script('UserControlSwitcher');
    }

    /**
     * {@inheritdoc}
     */
    public function display()
    {
        if ($this->visible) :
            return $this->viewer('switcher', $this->all());
        endif;

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function parse($attrs = [])
    {
        parent::parse($attrs);

        $this->set('attrs.aria-control', 'user_control-switcher');

        $this->set('ajax_action', 'user_control_switcher');

        $this->set('ajax_nonce', wp_create_nonce('UserControlSwitcher' . $this->get('name')));

        $this->set(
            'form.method',
            'post'
        );

        $this->set(
            'form.action',
            wp_nonce_url(
                $this->get('redirect_url'),
                'UserControl' . $this->get('name'),
                'csrf-token'
            )
        );


        $this->set(
            'role',
            array_merge(
                [
                    'name'      => 'role',
                    'value'     => -1,
                    'filter'    => false,
                    'removable' => false,
                ],
                $this->get('role', [])
            )
        );

        $this->set(
            'role.attrs.class',
            $this->get(
                'role.attrs.class',
                '%s UserControlSwitcher-select--role'
            )
        );

        $this->set(
            'user',
            array_merge(
                [
                    'name'      => 'user_id',
                    'value'     => -1,
                    'disabled'  => true,
                    'picker'    => [
                        'filter' => true
                    ],
                    'removable' => false,
                ],
                $this->get('user', [])
            )
        );

        $this->set(
            'user.attrs.class',
            $this->get(
                'user.attrs.class',
                '%s UserControlSwitcher-select--user'
            )
        );

        if (!$handler = $this->uc()->get($this->get('name'))) :
            return;
        elseif (!$handler->isAuth('switch')) :
            return;
        elseif (!$allowed_roles = $handler->getAllowedRoleList()) :
            return;
        else :
            $this->visible = true;

            $role_options = [];
            foreach ($allowed_roles as $allowed_role) :
                if (!$role = get_role($allowed_role)) :
                    continue;
                endif;
                $role_options[$allowed_role] = wordpress()->user()->roleDisplayName($allowed_role);
            endforeach;
            $role_options = [-1 => __('Choix du role', 'tify')] + $role_options;
            $this->set('role.choices', $role_options);

            $user_options = [-1 => __('Choix de l\'utilisateur', 'tify')];
            $this->set('user.choices', $user_options);

            $this->set(
                'attrs.data-options',
                rawurlencode(
                    json_encode(
                        [
                            'action'     => $this->get('ajax_action'),
                            'csrf-token' => $this->get('ajax_nonce'),
                            'user'       => $this->get('user'),
                        ],
                        JSON_FORCE_OBJECT
                    )
                )
            );
        endif;
    }

    /**
     * Récupération de la liste de selection des utilisateurs via Ajax.
     *
     * @return string
     */
    public function ajax()
    {
        check_ajax_referer('UserControlSwitcher' . request()->post('id'), 'csrf-token');

        $user = request()->post('user', []);
        $user = wp_unslash($user);

        $user['options'] = [-1 => __('Choix de l\'utilisateur', 'tify')];
        if ($user_options = wordpress()->user()->pluck(
            'display_name',
            'ID',
            [
                'role'   => request()->post('role', ''),
                'number' => -1,
            ]
        )) :
            $user['choices'] += $user_options;
            $user['disabled'] = false;
        else :
            $user['disabled'] = true;
        endif;

        echo field('select-js', $user);

        exit;
    }
}