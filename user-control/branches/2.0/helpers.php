<?php declare(strict_types=1);

use tiFy\Contracts\Partial\PartialFactory;

if (!function_exists('user_control_panel')) {
    /**
     * Panneau de prise de contrôle d'utilisateur.
     *
     * @param string Nom de qualification du controleur.
     * @param array Liste des attributs de configuration.
     *
     * @return tiFy\Plugins\UserControl\Contracts\PartialPanel|null
     */
    function user_control_panel(string $name, array $attrs = []): ?PartialFactory
    {
        return partial('user-control-panel', array_merge($attrs, ['name' => $name]));
    }
}

if (!function_exists('user_control_switcher')) {
    /**
     * Formulaire de bascule de prise de contrôle d'utilisateur.
     *
     * @param string Nom de qualification du controleur.
     * @param array Liste des attributs de configuration.
     *
     * @return tiFy\Plugins\UserControl\Contracts\PartialSwitcher|null
     */
    function user_control_switcher(string $name, array $attrs = []): ?PartialFactory
    {
        return partial('user-control-switcher', array_merge($attrs, ['name' => $name]));
    }
}

if (!function_exists('user_control_trigger')) {
    /**
     * Lien de prise de contrôle d'utilisateur ou de rétablissement de l'utilisateur original.
     *
     * @param string Nom de qualification du controleur.
     * @param array Liste des attributs de configuration.
     *
     * @return tiFy\Plugins\UserControl\Contracts\PartialPanel|null
     */
    function user_control_trigger(string $name, array $attrs = []): ?PartialFactory
    {
        return partial('user-control-trigger', array_merge($attrs, ['name' => $name]));
    }
}