<?php

namespace tiFy\Plugins\UserControl\Contracts;

use League\Event\EventInterface;
use tiFy\Contracts\Kernel\ParametersBagInterface;
use WP_User;

interface UserControlItemHandlerInterface extends ParametersBagInterface
{
    /**
     * Vérification de permission d'appel de prise de contrôle du compte d'un utilisateur (called) par un autre (caller).
     *
     * @param \WP_User $caller Objet utilisateur de l'appelant.
     * @param \WP_User $called Objet utilisateur de l'appelé.
     *
     * @return bool
     */
    public function can($caller, $called);

    /**
     * Evenement de vérification de permission d'appel de prise de contrôle du compte d'un utilisateur (called) par un autre (caller).
     *
     * @param WP_User $caller Objet utilisateur de l'appelant.
     * @param WP_User $called Objet utilisateur de l'appelé.
     * @param EventInterface $event
     *
     * @return void
     */
    public function eventCan(WP_User $caller, WP_User $called, EventInterface $event);

    /**
     * @return array
     */
    public function getAllowedRoleList();

    /**
     * Récupération du nom de qualification du controleur.
     *
     * @return string
     */
    public function getName();

    /**
     * Récupération des données utilisateurs selon son ID, son login ou l'object Wordpress \WP_User.
     *
     * @param int|string|\WP_User $user Utilisateur à récupérer.
     *
     * @return \WP_User
     */
    public function getUserData($user);

    /**
     * Vérification des permissions de prise de contrôle d'un utilisateur.
     *
     * @param WP_User $user Utilisateur à contrôler.
     *
     * @return
     */
    public function isAllowed($user);

    /**
     * Vérification des autorisations de l'utilisateur principal courant
     *
     * @param string $action Type d'action. 'switch': prise de contrôle d'un utilisateur|'restore': Récupération de l'utilsateur principal.
     *
     * @return bool|WP_User
     */
    public function isAuth($action = 'switch');
}