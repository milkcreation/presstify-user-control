<?php

use tiFy\Plugins\UserControl\Contracts\UserControlPartialController;

if (!function_exists('user_control_panel')) :
    /**
     * Panneau de prise de contrôle d'utilisateur.
     *
     * @param string Nom de qualification du controleur.
     * @param array Liste des attributs de configuration.
     *
     * @return UserControlPartialController
     */
    function user_control_panel($name, $attrs = [])
    {
        return partial('user-control.panel', array_merge($attrs, ['name' => $name]));
    }
endif;


if (!function_exists('user_control_switcher')) :
    /**
     * Formulaire de bascule de prise de contrôle d'utilisateur.
     *
     * @param string Nom de qualification du controleur.
     * @param array Liste des attributs de configuration.
     *
     * @return UserControlPartialController
     */
    function user_control_switcher($name, $attrs = [])
    {
        return partial('user-control.switcher', array_merge($attrs, ['name' => $name]));
    }
endif;

if (!function_exists('user_control_trigger')) :
    /**
     * Lien de prise de contrôle d'utilisateur ou de rétablissement de l'utilisateur original.
     *
     * @param string Nom de qualification du controleur.
     * @param array Liste des attributs de configuration.
     *
     * @return UserControlPartialController
     */
    function user_control_trigger($name, $attrs = [])
    {
        return partial(
            'user-control.trigger',
            array_merge($attrs, ['name' => $name])
        );
    }
endif;