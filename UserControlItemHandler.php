<?php

namespace tiFy\Plugins\UserControl;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use tiFy\Kernel\Parameters\AbstractParametersBag;
use tiFy\Plugins\UserControl\Contracts\UserControlItemHandlerInterface;
use WP_User;


class UserControlItemHandler extends AbstractParametersBag implements UserControlItemHandlerInterface
{
    /**
     * Liste des attributs de configuration.
     * @param array $attributes {
     *      @var array $auth_roles Liste des rôles autorisés à prendre le contrôle d'un utilsateur. ['administrator'] si non pas défini.
     *      @var array $auth_users Liste des utilisateurs autorisés à prendre le contrôle d'un autre utlisateur. vide par défaut.
     *      @var array $allowed_roles Liste des rôles utilisateurs pour lesquels la prise de contrôle est autorisés. ['subscriber'] si non pas défini.
     *      @var array $allowed_users Liste des utilisateurs pour lesquels la prise de contrôle est permise. vide par défaut.
     * }
     */
    protected $attributes = [
        'auth_roles'    => ['administrator'],
        'auth_users'    => [],
        'allowed_roles' => ['subscriber'],
        'allowed_users' => []
    ];

    /**
     * Nom du cookie d'authentication pour la conservation de l'utilisateur principal.
     * @var string
     */
    protected $authCookieName = '';

    /**
     * Droit de prise de contrôle de compte d'un utilisateur par l'utilisateur courant.
     * @var bool
     */
    protected $can = false;

    /**
     * Nom du cookie de connection pour la conservation de l'utilisateur principal.
     * @var string
     */
    protected $loggedInCookieName = '';

    /**
     * Nom de qualification du controleur.
     * @var string
     */
    protected $name = '';

    /**
     * CONSTRUCTEUR
     *
     * @param string $id Identifiant de qualification

     *
     * @return void
     */
    public function __construct($name, $attrs = [])
    {
        $this->name = $name;

        parent::__construct($attrs);

        $this->authCookieName = 'user_control_auth_cookie_' . COOKIEHASH;
        $this->loggedInCookieName = 'user_control_logged_in_' . COOKIEHASH;

        events()->listen('user_control.can.' . $this->getName(), [$this, 'eventCan']);

        add_action(
            'wp_loaded',
            function () {
                if (!is_user_logged_in()) :
                    $this->_clearCookies();
                elseif (wp_verify_nonce(request()->get('csrf-token'), 'UserControl' . $this->getName())) :
                    switch(request()->get('action', '')) :
                        // Prise de contrôle du compte d'un utilisateur
                        case 'switch' :
                            $user_id = request()->get('user_id', 0);

                            if (!$this->_can($user_id)) :
                                wp_die(
                                    __(
                                        'Vous ne disposez pas des habilitations suffisantes pour effectuer cette action.',
                                        'tify'
                                    ),
                                    __('Habilitations insuffisantes', 'tify'),
                                    500
                                );
                            endif;

                            $this->_handleSwitch($user_id);

                            wp_redirect(request()->get('_wp_http_referer', home_url('/')));
                            exit;
                            break;

                        // Récupération de l'utilisateur principal
                        case 'restore' :
                            if (!$this->_handleRestore()) :
                                \wp_die(
                                    __(
                                        'Vous ne disposez pas des habilitations suffisantes pour effectuer cette action.',
                                        'tify'
                                    ),
                                    __('Habilitations insuffisantes', 'tify'),
                                    500
                                );
                            endif;

                            wp_redirect(home_url('/'));
                            exit;
                            break;

                        // Action non définie
                        default :
                            wp_die(
                                __(
                                    'Il semblerait que tout ne se soit pas vraiment déroulé comme prévu ?!',
                                    'tify'
                                ),
                                __('Erreur de traitement', 'tify'),
                                500
                            );
                            break;
                    endswitch;
                endif;
            }
        );
    }

    /**
     * Vérification de la permission de prise de contrôle d'un utilisateur par l'utilisateur principal courant.
     *
     * @param WP_User $wp_user Utilisateur à contrôler.
     *
     * @return
     */
    private function _can($user)
    {
        if (!$this->isAuth('switch')) :
            $this->can = false;
        elseif (!$this->isAllowed($user)) :
            $this->can = false;
        else :
            $caller = wp_get_current_user();
            $called = $this->getUserData($user);

            events()->trigger('user_control.can.' . $this->getName(), [$caller, $called]);
        endif;

        return $this->can;
    }

    /**
     * Contrôle des cookies d'authentification et récupération de l'utilisateur principal.
     *
     * @return int
     */
    private function _checkCookies()
    {
        if (
            (!$auth_cookie = request()->cookie($this->authCookieName, '')) ||
            (!$logged_in_cookie = request()->cookie($this->loggedInCookieName, ''))
        ) :
            return 0;
        endif;

        if (!wp_validate_auth_cookie($auth_cookie, (is_ssl() ? 'secure_auth' : 'auth'))) :
            return 0;
        endif;

        if (!$user_id = wp_validate_auth_cookie($logged_in_cookie, 'logged_in')) :
            return 0;
        endif;

        return $user_id;
    }

    /**
     * Suppression de la liste des cookies.
     *
     * @return void
     */
    private function _clearCookies()
    {
        $secure = ('https' === \parse_url(home_url(), PHP_URL_SCHEME));

        $response = new Response();
        $response->headers->clearCookie(
            $this->authCookieName,
            PLUGINS_COOKIE_PATH,
            COOKIE_DOMAIN,
            $secure
        );
        $response->headers->clearCookie(
            $this->authCookieName,
            ADMIN_COOKIE_PATH,
            COOKIE_DOMAIN,
            $secure
        );
        $response->headers->clearCookie(
            $this->loggedInCookieName,
            COOKIEPATH,
            COOKIE_DOMAIN,
            $secure
        );
        if (COOKIEPATH != SITECOOKIEPATH) :
            $response->headers->clearCookie(
                $this->loggedInCookieName,
                SITECOOKIEPATH,
                COOKIE_DOMAIN,
                $secure
            );
        endif;

        $response->send();
    }

    /**
     * Récupération de l'utilisateur principal en tant qu'utilisateur courant.
     *
     * @return bool
     */
    private function _handleRestore()
    {
        if (!$user = $this->isAuth('restore')) :
            return false;
        endif;

        events()->trigger('user_control.restore.' . $this->getName(), [$user]);

        $this->_clearCookies();
        wp_clear_auth_cookie();
        wp_set_auth_cookie((int)$user->ID);

        return true;
    }

    /**
     * Prise de contrôle d'un compte utilisateur
     *
     * @param int|WP_User $user
     *
     * @return void
     */
    private function _handleSwitch($user)
    {
        if (!\is_user_logged_in()) :
            return;
        endif;

        if ($user instanceof WP_User) :
        else :
            $user = get_userdata($user);
        endif;

        if (!$user instanceof WP_User) :
            return;
        endif;

        events()->trigger('user_control.switch.' . $this->getName(), [$user]);

        if ($this->_setCookies()) :
            wp_set_auth_cookie((int)$user->ID);
        endif;
    }

    /**
     * Définition des Cookies
     *
     * @var string $cookie_name Identification de qualification du cookie
     * @var int $cookie_expire Nombre de secondes avant expiration du cookie
     *
     * @return bool
     */
    private function _setCookies()
    {
        // Bypass - Vérification des autorisations utilisateur
        if (is_blog_admin() || is_network_admin() || empty($_COOKIE[LOGGED_IN_COOKIE])) :
            return false;
        endif;

        // Bypass - Récupération des données d'authentification
        if (!$auth_datas = wp_parse_auth_cookie()) :
            return false;
        endif;

        // Bypass - Récupération des données de connection
        if (!$logged_in_datas = wp_parse_auth_cookie($_COOKIE[LOGGED_IN_COOKIE], 'logged_in')) :
            return false;
        endif;

        // Définition des données de cookie d'authentification et de connection
        $auth_cookie = $auth_datas['username'] . '|' . $auth_datas['expiration'] . '|' . $auth_datas['token'] . '|' . $auth_datas['hmac'];
        $logged_in_cookie = $logged_in_datas['username'] . '|' . $logged_in_datas['expiration'] . '|' . $logged_in_datas['token'] . '|' . $logged_in_datas['hmac'];

        // Définition de la sécurité des cookies
        $secure = ('https' === \parse_url(home_url(), PHP_URL_SCHEME));

        // Génération des cookies de conservation de l'utilisateur principal
        $response = new Response();
        $response->headers->setCookie(
            new Cookie(
                $this->authCookieName,
                $auth_cookie,
                0,
                PLUGINS_COOKIE_PATH,
                COOKIE_DOMAIN,
                $secure
            )
        );
        $response->headers->setCookie(
            new Cookie(
                $this->authCookieName,
                $auth_cookie,
                0,
                ADMIN_COOKIE_PATH,
                COOKIE_DOMAIN,
                $secure
            )
        );
        $response->headers->setCookie(
            new Cookie(
                $this->loggedInCookieName,
                $logged_in_cookie,
                0,
                COOKIEPATH,
                COOKIE_DOMAIN,
                $secure
            )
        );
        if (COOKIEPATH != SITECOOKIEPATH) :
            $response->headers->setCookie(
                new Cookie(
                    $this->loggedInCookieName,
                    $logged_in_cookie,
                    0,
                    SITECOOKIEPATH,
                    COOKIE_DOMAIN,
                    $secure
                )
            );
        endif;

        // Envoi de la réponse
        $send = $response->send();

        // Récupération de la liste des cookies
        $cookies = $send->headers->getCookies();

        // Retour du succès de création des cookies
        return !empty($cookies);
    }

    /**
     * {@inheritdoc}
     */
    public function can($caller, $called)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function eventCan(WP_User $caller, WP_User $called)
    {
        if ($event->getName() !== 'user_control.can.' . $this->getName()) :
            $this->can = false;
        else :
            $this->can = $this->can($caller, $called);
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedRoleList()
    {
        return $this->get('allowed_roles', []);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserData($user)
    {
        if (is_a($user, 'WP_User')) :
            return $user;
        elseif (is_numeric($user)) :
            return \get_userdata((int)$user);
        elseif (is_string($user)) :
            return \get_user_by('login', $user);
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed($user)
    {
        if (!$user = $this->getUserData($user)) :
            return false;
        endif;

        // Vérification des autorisations pour le rôle de l'utilisateur courant
        if (!array_intersect($user->roles, $this->get('allowed_roles', []))) :
            return false;
        endif;

        // Vérification des autorisations parmis la liste des utilisateurs habilités
        if ($allowed_users = $this->get('allowed_users', [])) :
            $users = [];

            foreach($allowed_users as $allowed_user) :
                if (!$user_data = $this->getUserData($allowed_user)) :
                    continue;
                endif;
                $users[] = $user_data;
            endforeach;

            if (!in_array($user, $users)) :
                return false;
            endif;
        endif;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuth($action = 'switch')
    {
        if (!is_user_logged_in()) :
            return false;
        endif;

        // Récupération de l'utilisateur principale courant
        if ($action === 'switch') :
            $user = \wp_get_current_user();
        elseif(($action === 'restore') && ($user_id = $this->_checkCookies())) :
            $user = get_userdata($user_id);
        else :
            return false;
        endif;

        // Test d'intégrité de l'utilisateur récupéré
        if (!is_a($user, 'WP_User')) :
            return false;
        endif;

        // Vérification des autorisations pour le rôle de l'utilisateur courant
        if (!array_intersect($user->roles, $this->get('auth_roles', []))) :
            return false;
        endif;

        // Vérification des autorisations parmis la liste des utilisateurs habilités
        if ($auth_users = $this->get('auth_users', [])) :
            $users = [];

            foreach($auth_users as $auth_user) :
                if (!$user_data = $this->getUserData($auth_user)) :
                    continue;
                endif;
                $users[] = $user_data;
            endforeach;

            if (!in_array($user, $users)) :
                return false;
            endif;
        endif;

        return $user;
    }
}