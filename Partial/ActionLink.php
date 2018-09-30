<?php

/**
 * @name UserControl - ActionLink.
 * @desc Controleur d'affichage de lien de récupération de l'utilisateur principal ou de bascule de compte utilisateur.
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Partial\AbstractPartialItem;
use tiFy\Partial\Partial;
use tiFy\Plugins\UserControl\UserControl;

class ActionLink extends AbstractPartialItem
{
    /**
     * Liste des attributs de configuration.
     * @var array $attrs {
     *      @var string $take_over_id Identifiant de qualification du contrôleur d'affichage (requis).
     *      @var int $user_id Identifiant de qualification de l'utilisateur (requis). Mais uniquement pour l'action 'switch'.
     *      @var string $action $action Type d'action. 'switch': prise de contrôle d'un utilisateur|'restore': Récupération de l'utilsateur principal (défaut).
     *      @var string $text Texte du bouton.
     *      @var array $attrs Attributs de la balise du lien. Hors 'href' défini automatiquement par le controleur.
     *      @var string $redirect_url Url de redirection après l'action.
     * }
     */
    protected $attributes = [
        'take_over_id'  => '',
        'user_id'       => 0,
        'action'        => 'restore',
        'text'          => '',
        'attrs'         => [],
        'redirect_url'  => ''
    ];

    /**
     * Traitement des attributs de configuration.
     *
     * @param array $attrs Liste des attributs de configuration personnalisés.
     *
     * @return array
     */
    protected function parse($attrs = [])
    {
        $this->set('redirect_url', home_url('/'));

        parent::parse($attrs);
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
        endif;

        $action = $this->get('action');

        switch($action) :
            case 'switch' :
                // Bypass - L'utilisateur principal n'est pas habilité à utiliser l'interface
                if (!$takeOverController->isAuth($action)) :
                    return;
                // Bypass - L'utilisateur n'existe pas
                elseif (!$user = $takeOverController->getUserData($user_id)) :
                    return;
                // Bypass - L'utilisateur n'est pas habilité à prendre le contrôle
                elseif (!$takeOverController->isAllowed($user->ID)) :
                    return;
                endif;

                if (!$this->get('text')) :
                    $this->set('text', __('Naviguer comme', 'tify'));
                endif;

                if (!$this->get('attrs.title')) :
                    $this->set('attrs.title', sprintf(__('Naviguer sur le site en tant que %s', 'tify'), $user->display_name));
                endif;

                $this->set(
                    'attrs.href',
                    add_query_arg(
                        [
                            'action' => $this->get('action'),
                            'tfy_take_over_id' => $this->get('take_over_id'),
                            'user_id' => $user->ID
                        ],
                        wp_nonce_url($this->get('redirect_url'), 'tiFyTakeOver-switch')
                    )
                );
                break;
            case 'restore' :
                // Bypass - L'utilisateur n'est pas autorisé à utiliser l'interface
                if (!$takeOverController->isAuth($action)) :
                    return;
                endif;

                if (!$this->get('text')) :
                    $this->set('text', __('Rétablir', 'tify'));
                endif;

                if (!$this->get('attrs.title')) :
                    $this->set('attrs.title', __('Rétablissement l\'utilisateur principal', 'tify'));
                endif;

                $this->set(
                    'attrs.href',
                    add_query_arg(
                        [
                            'action' => $this->get('action'),
                            'tfy_take_over_id' => $this->get('take_over_id')
                        ],
                        \wp_nonce_url($this->get('redirect_url'), 'tiFyTakeOver-restore')
                    )
                );
                break;
            default:
                return;
                break;
        endswitch;

        if (!$this->get('attrs.class')) :
            $this->set('attrs.class', 'tiFyTakeOver-ActionLink tiFyTakeOver-ActionLink--' . $this->get('action'));
        endif;

        return (string) Partial::Tag(
            [
                'tag'       => 'a',
                'attrs'     => $this->get('attrs', []),
                'content'   => $this->get('text')
            ]
        );
    }
}