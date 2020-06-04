<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Contracts;

use League\Event\EventInterface;
use tiFy\Contracts\Support\ParamsBag;
use WP_User;

interface UserControlFactory extends ParamsBag
{
    /**
     * Vérification de permission d'appel de prise de contrôle du compte d'un utilisateur (called) par un autre (caller).
     *
     * @param WP_User $caller Objet utilisateur de l'appelant.
     * @param WP_User $called Objet utilisateur de l'appelé.
     *
     * @return boolean
     */
    public function can($caller, $called): bool;

    /**
     * Evenement de vérification de permission d'appel de prise de contrôle du compte d'un utilisateur (called) par un
     * autre (caller).
     *
     * @param WP_User $caller Objet utilisateur de l'appelant.
     * @param WP_User $called Objet utilisateur de l'appelé.
     * @param EventInterface $event
     *
     * @return void
     */
    public function eventCan(WP_User $caller, WP_User $called, EventInterface $event): void;

    /**
     * @return array
     */
    public function getAllowedRoleList(): array;

    /**
     * Récupération du nom de qualification du controleur.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Récupération des données utilisateurs selon son ID, son login ou l'object Wordpress \WP_User.
     *
     * @param int|string|WP_User $user Utilisateur à récupérer.
     *
     * @return WP_User|null
     */
    public function getUserData($user): ?WP_User;

    /**
     * Vérification des permissions de prise de contrôle d'un utilisateur.
     *
     * @param WP_User $user Utilisateur à contrôler.
     *
     * @return boolean
     */
    public function isAllowed(WP_User $user): bool;

    /**
     * Vérification des autorisations de l'utilisateur principal courant
     *
     * @param string $action Type d'action. 'switch': prise de contrôle d'un utilisateur|'restore': Récupération de l'utilsateur principal.
     *
     * @return bool
     */
    public function isAuth($action = 'switch'): bool;
}