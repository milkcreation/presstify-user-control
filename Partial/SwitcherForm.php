<?php

/**
 * @name UserControl - SwitcherForm.
 * @desc Controleur d'affichage de fomulaire de bascule de prise de contrôle d'un utilisateur.
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Field\Field;
use tiFy\Partial\AbstractPartialItem;
use tiFy\Kernel\Tools;
use tiFy\Plugins\UserControl\UserControl;
use tiFy\Lib\User\User;

class SwitcherForm extends AbstractPartialItem
{
    /**
     * Liste des attributs de configuration.
     * @var array $attrs {
     *      @var string $take_over_id Identifiant de qualification du contrôleur d'affichage.
     *      @var array $fields {
     *          Liste des champs de selection role et utilisateur.
     *
     *          @var array $role {
     *              Attributs de configuration du champ de selection des roles.
     *              @see \tiFy\Field\SelectJs\SelectJs
     *          }
     *          @var array $user {
     *              Attributs de configuration du champ de selection des utilisateurs.
     *              @see \tiFy\Field\SelectJs\SelectJs
     *          }
     *      }
     * }
     */
    protected $attributes = [
        'take_over_id' => '',
        'fields'       => [
            'role'  => [],
            'user'  => []
        ]
    ];

    /**
     * Initialisation globale de Wordpress.
     *
     * @return void
     */
    public function init()
    {
        // Actions ajax
        $this->appAddAction(
            'wp_ajax_tiFyTakeOverSwitcherForm_get_users',
            'wp_ajax_get_users'
        );
        $this->appAddAction(
            'wp_ajax_nopriv_tiFyTakeOverSwitcherForm_get_users',
            'wp_ajax_get_users'
        );

        \wp_register_script(
            'tiFyTakeOver-SwitcherForm',
            $this->appAssetUrl('/User/TakeOver/SwitcherForm/js/scripts.js', get_class()),
            [],
            171218,
            true
        );
    }

    /**
     * Mise en file des scripts.
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        Field::enqueue('tiFyTakeOver-SwitcherForm');
        \wp_enqueue_script('tify_control-take_over_switcher_form');
    }

    /**
     * Récupération de la liste de selection des utilisateurs via Ajax.
     *
     * @return string
     */
    public function wp_ajax_get_users()
    {
        // Contrôle de sécurité
        check_ajax_referer('tiFyTakeOverSwitcherForm-getUsers');

        // Récupération des attributs de champ
        $fields = $this->appRequest('POST')->get('fields', ['role' => [], 'user' => []]);
        $fields = wp_unslash($fields);

        // Récupération de la liste de choix des utilisateurs
        if ($user_options = Tools::User()->pluck(
            'display_name',
            'ID',
            [
                'role'      => $this->appRequest('POST')->get('role', ''),
                'number'    => -1
            ]
        )) :
            array_map(function($item){ $item[0] = (string)$item[0]; return $item;}, $user_options);
        endif;
        $disabled = empty($user_options);

        $user_options = ['-1' => __('Choix de l\'utilisateur', 'tify')] + $user_options;

        $fields['user']['options'] = $user_options;
        $fields['user']['disabled'] = $disabled;

        echo Field::SelectJs($fields['user']);

        exit;
    }

    /**
     * Traitement des attributs de configuration.
     *
     * @param array $attrs Liste des attributs de configuration personnalisés.
     *
     * @return array
     */
    protected function parse($attrs = [])
    {
        $this->attributes['redirect_url'] = home_url('/');

        parent::parse($attrs);

        $this->set('ajax_action', 'tiFyTakeOverSwitcherForm_get_users');

        $this->set('ajax_nonce', \wp_create_nonce('tiFyTakeOverSwitcherForm-getUsers'));

        $this->set(
            'fields.role',
            array_merge(
                [
                    'name'            => 'role',
                    'value'           => -1,
                    'filter'          => false,
                    'removable'       => false
                ],
                $this->get('fields.role', [])
            )
        );
        $this->set(
            'fields.role.attrs.class',
            $this->get(
                'fields.role.attrs.class',
                '%s tiFyTakeOverSwitcherForm-selectField--role'
            )
        );

        $this->set(
            'fields.user',
            array_merge(
                [
                    'name'            => 'user_id',
                    'value'           => -1,
                    'disabled'        => true,
                    'picker'          => [
                        'filter'    => true
                    ],
                    'removable'       => false
                ],
                $this->get('fields.user', [])
            )
        );
        $this->set(
            'fields.user.attrs.class',
            $this->get(
                'fields.user.attrs.class',
                '%s tiFyTakeOverSwitcherForm-selectField--user'
            )
        );
    }

    /**
     * Affichage.
     *
     * @return string
     */
    public function display()
    {
        if (!$takeOverController = $this->appServiceGet(TakeOver::class)->get($this->get('take_over_id'))) :
            return;
        elseif (!$takeOverController->isAuth('switch')) :
            return;
        elseif (!$allowed_roles = $takeOverController->getAllowedRoleList()) :
            return;
        endif;

        $role_options = [];
        foreach ($allowed_roles as $allowed_role) :
            if (!$role = \get_role($allowed_role)) :
                continue;
            endif;
            $role_options[$allowed_role] = Tools::User()->roleDisplayName($allowed_role);
        endforeach;
        $role_options = ['-1' => __('Choix du role', 'tify')] + $role_options;

        $user_options = ['-1' => __('Choix de l\'utilisateur', 'tify')];

        $data_options = [
            'ajax_action' => $this->get('ajax_action'),
            'ajax_nonce'  => $this->get('ajax_nonce'),
            'fields'      => $this->get('fields')
        ];

        $output = "";
        $output .= "<form class=\"tiFyTakeOver-Control--switch_form\" method=\"post\" action=\"\" data-options=\"" . rawurlencode(json_encode($data_options)) . "\" >";
        $output .= \wp_nonce_field('tiFyTakeOver-switch', '_wpnonce', false, false);
        $output .= Field::Hidden(
            [
                'name'  => 'action',
                'value' => 'switch',
            ]
        );
        $output .= Field::Hidden(
            [
                'name'  => 'tfy_take_over_id',
                'value' => $this->get('take_over_id'),
            ]
        );
        $output .= Field::SelectJs(
            array_merge(
                [
                    'options' => $role_options
                ],
                $this->get('fields.role', [])
            )
        );
        $output .= Field::SelectJs(
            array_merge(
                [
                    'options' => $user_options
                ],
                $this->get('fields.user', [])
            )
        );
        $output .= "</form>";

        return $output;
    }
}