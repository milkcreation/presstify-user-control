<?php

/**
 * @name UserControl
 * @desc Prise de controle de compte utilisateur.
 * @package presstify-plugins/user-control
 * @namespace tiFy\Plugins\UserControl
 * @version 2.0.0
 * @author Jordy Manner <jordy@tigreblanc.fr>
 * @copyright Milkcreation
 */

namespace tiFy\Plugins\UserControl;

class UserControl
{
    public function __construct()
    {
        exit;
    }

    /**
     * Initialisation du controleur.
     *
     * @return void
     */
    public function appBoot()
    {
        // Activation des permissions de prises de contrôle de comptes utilisateurs
        if ($take_over = $this->appConfig('take_over', [], User::class)) :
            foreach ($take_over as $id => $attrs) :
                $this->register($id, $attrs);
            endforeach;
        endif;

        $this->appAddAction('init');
    }

    /**
     * Déclaration de controleur d'affichage.
     *
     * @param Partial $partialController Classe de rappel des controleurs d'affichage.
     *
     * @return void
     */
    public function tify_partial_register($partialController)
    {
        $partialController->register(
            'TakeOverActionLink',
            ActionLink::class . "::make"
        );
        $partialController->register(
            'TakeOverAdminBar',
            AdminBar::class . "::make"
        );
        $partialController->register(
            'TakeOverSwitcherForm',
            SwitcherForm::class . "::make"
        );
    }

    /**
     * Déclaration des classes de rappel de prise de contrôle de compte utilisateur
     *
     * @param string $name Nom de qualification.
     * @param array $attrs Attributs de configuration.
     *
     * @return null|TakeOverController
     */
    public function register($name, $attrs = [])
    {
        $alias = "tfy.user.take_over.{$name}";
        if ($this->appServiceHas($alias)) :
            return;
        endif;

        $this->appServiceShare($alias, new TakeOverController($name, $attrs));

        return $this->appServiceGet($alias);
    }

    /**
     * Récupération des classes de rappel de prise de contrôle de compte utilisateur
     *
     * @param string $name Identifiant de qualification
     *
     * @return null|TakeOverController
     */
    public function get($name)
    {
        $alias = "tfy.user.take_over.{$name}";
        if ($this->appServiceHas($alias)) :
            return $this->appServiceGet($alias);
        endif;
    }
}
