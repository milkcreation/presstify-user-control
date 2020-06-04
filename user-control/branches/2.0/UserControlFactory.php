<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl;

use tiFy\Http\Response;
use tiFy\Support\ParamsBag;
use tiFy\Support\Proxy\{Cookie, Partial, Redirect, Request, Router};
use tiFy\Plugins\UserControl\Contracts\{
    UserControl,
    UserControlFactory as UserControlFactoryContract
};
use WP_User;

class UserControlFactory extends ParamsBag implements UserControlFactoryContract
{
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
     * Instance du gestionnaire de plugin.
     * @var UserControl
     */
    protected $userControl;

    public $route;

    /**
     * CONSTRUCTEUR
     *
     * @param string $name Identifiant de qualification.
     * @param array $attrs Liste des attributs de configuration.
     * @param UserControl $userControl Instance du gestionnaire de plugin.
     *
     * @return void
     */
    public function __construct(string $name, array $attrs, UserControl $userControl)
    {
        $this->userControl = $userControl;
        $this->name = $name;

        $this->set($attrs)->parse();

        $this->authCookieName = 'user_control_auth_cookie_' . COOKIEHASH;
        $this->loggedInCookieName = 'user_control_logged_in_' . COOKIEHASH;

        events()->listen('user_control.can.' . $this->getName(), [$this, 'eventCan']);

        add_filter('user_row_actions', function (array $actions, WP_User $user) {
            if ($trigger = Partial::get('user-control-trigger', [
                'action'  => 'switch',
                'name'    => $this->getName(),
                'user_id' => $user->ID,
            ])->render()) {
                $actions['user-control'] = $trigger;
            }

            return $actions;
        }, 999999, 2);

        $this->route = Router::get(md5("UserControl{$name}"), function () {
            //if (wp_verify_nonce(Request::input('csrf-token'), 'UserControl' . $this->getName())) {
                switch (Request::input('action', '')) {
                    // Prise de contrôle du compte d'un utilisateur
                    case 'switch' :
                        $user_id = Request::input('user_id', 0);

                        if (!$this->_can($user_id)) {
                            return new Response(_default_wp_die_handler(
                                __(
                                    'Vous ne disposez pas des habilitations suffisantes pour effectuer cette action.',
                                    'tify'
                                ),
                                __('Habilitations insuffisantes', 'tify'),
                                500
                            ));
                        } else {
                            $this->_handleSwitch($user_id);

                            return Redirect::to(Request::input('_wp_http_referer', home_url('/')));
                        }
                        break;
                    // Récupération de l'utilisateur principal
                    case 'restore' :
                        if (!$this->_handleRestore()) {
                            return new Response(_default_wp_die_handler(
                                __(
                                    'Vous ne disposez pas des habilitations suffisantes pour effectuer cette action.',
                                    'tify'
                                ),
                                __('Habilitations insuffisantes', 'tify'),
                                500
                            ));
                        } else {
                            return Redirect::to(Request::input('_wp_http_referer', home_url('/')));
                        }
                        break;
                    // Action non définie
                    default :
                        return new Response(_default_wp_die_handler(
                            __(
                                'Il semblerait que tout ne se soit pas vraiment déroulé comme prévu ?!',
                                'tify'
                            ),
                            __('Erreur de traitement', 'tify'),
                            500
                        ));
                        break;
                }
            //}
        });

        add_action('wp_loaded', function () {
            if (!is_user_logged_in()) {
                $this->_clearCookies();
            }
        });
    }

    /**
     * Vérification de la permission de prise de contrôle d'un utilisateur par l'utilisateur principal courant.
     *
     * @param WP_User|int $user Utilisateur à contrôler.
     *
     * @return boolean
     */
    private function _can($user): bool
    {
        if (!$this->isAuth('switch')) {
            $this->can = false;
        } elseif (!$this->isAllowed($user)) {
            $this->can = false;
        } else {
            $caller = wp_get_current_user();
            $called = $this->getUserData($user);

            events()->trigger('user_control.can.' . $this->getName(), [$caller, $called]);
        }

        return !!$this->can;
    }

    /**
     * Contrôle des cookies d'authentification et récupération de l'utilisateur principal.
     *
     * @return int
     */
    private function _checkCookies()
    {
        $auth = Cookie::make($this->authCookieName, ['salt' =>'', 'base64' => true])->get();
        $loggedin = Cookie::make($this->loggedInCookieName, ['salt' =>'', 'base64' => true])->get();

        if (!$auth && !$loggedin) {
            return 0;
        }

        unset($auth['scheme']);
        unset($loggedin['scheme']);

        $user_id = 0;
        if ($auth) {
            $user_id = wp_validate_auth_cookie(implode('|',$auth), (is_ssl() ? 'secure_auth' : 'auth'));
        }

        if (!$user_id && $loggedin) {
            $user_id = wp_validate_auth_cookie(implode('|', $loggedin), 'logged_in');
        }

        return $user_id;
    }

    /**
     * Suppression de la liste des cookies.
     *
     * @return void
     */
    private function _clearCookies()
    {
        Cookie::make(md5($this->authCookieName), [
            'name'   =>  $this->authCookieName,
            'path'   => PLUGINS_COOKIE_PATH,
            'domain' => COOKIE_DOMAIN,
            'salt'   => '',
            'base64' => true
        ])->clear();

        Cookie::make(md5($this->authCookieName . 'admin'), [
            'name'   =>  $this->authCookieName,
            'path'   => ADMIN_COOKIE_PATH,
            'domain' => COOKIE_DOMAIN,
            'salt'   => '',
        ])->clear();

        Cookie::make(md5($this->loggedInCookieName), [
            'name'   =>  $this->loggedInCookieName,
            'path'   => COOKIEPATH,
            'domain' => COOKIE_DOMAIN,
            'salt'   => ''
        ])->clear();

        if (COOKIEPATH != SITECOOKIEPATH) {
            Cookie::make(md5($this->loggedInCookieName . 'site'), [
                'name'   =>  $this->loggedInCookieName,
                'path'   => SITECOOKIEPATH,
                'domain' => COOKIE_DOMAIN,
                'salt'   => ''
            ])->clear();
        }
    }

    /**
     * Récupération de l'utilisateur principal en tant qu'utilisateur courant.
     *
     * @return bool
     */
    private function _handleRestore()
    {
        if (!$user = $this->getAuth('restore')) {
            return false;
        }

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
        if (!is_user_logged_in()) {
            return;
        } elseif (!$user instanceof WP_User) {
            $user = get_userdata($user);
        }

        if (!$user instanceof WP_User) {
            return;
        } else {
            events()->trigger('user_control.switch.' . $this->getName(), [$user]);

            if ($this->_setCookies()) {
                wp_set_auth_cookie((int)$user->ID);
            }
        }
    }

    /**
     * Définition des Cookies
     *
     * @return bool
     * @var int $cookie_expire Nombre de secondes avant expiration du cookie
     *
     * @var string $cookie_name Identification de qualification du cookie
     */
    private function _setCookies()
    {
        if (is_blog_admin() || is_network_admin() || empty($_COOKIE[LOGGED_IN_COOKIE])) {
            return false;
        } elseif (!$auth_datas = wp_parse_auth_cookie('', 'logged_in')) {
            return false;
        } elseif (!$logged_in_datas = wp_parse_auth_cookie($_COOKIE[LOGGED_IN_COOKIE], 'logged_in')) {
            return false;
        }

        //$auth_cookie = $auth_datas['username'] . '|' . $auth_datas['expiration'] . '|' . $auth_datas['token'] . '|' . $auth_datas['hmac'];
        //$logged_in_cookie = $logged_in_datas['username'] . '|' . $logged_in_datas['expiration'] . '|' . $logged_in_datas['token'] . '|' . $logged_in_datas['hmac'];

        Cookie::make(md5($this->authCookieName), [
            'name'   => $this->authCookieName,
            'path'   => PLUGINS_COOKIE_PATH,
            'domain' => COOKIE_DOMAIN,
            'salt'   => '',
            'base64' => true
        ])->set($auth_datas);

        Cookie::make(md5($this->authCookieName . 'admin'), [
            'name'   =>  $this->authCookieName,
            'path'   => ADMIN_COOKIE_PATH,
            'domain' => COOKIE_DOMAIN,
            'salt'   => '',
            'base64' => true
        ])->set($auth_datas);

        Cookie::make(md5($this->loggedInCookieName), [
            'name'   =>  $this->loggedInCookieName,
            'path'   => COOKIEPATH,
            'domain' => COOKIE_DOMAIN,
            'salt'   => '',
            'base64' => true
        ])->set($logged_in_datas);

        if (COOKIEPATH != SITECOOKIEPATH) {
            Cookie::make(md5($this->loggedInCookieName . 'site'), [
                'name'   =>  $this->loggedInCookieName,
                'path'   => SITECOOKIEPATH,
                'domain' => COOKIE_DOMAIN,
                'salt'   => '',
                'base64' => true
            ])->set($logged_in_datas);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function can($caller, $called): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @return {
     * @var array $auth_roles Liste des rôles autorisés à prendre le contrôle d'un utilsateur.
     *                             ['administrator'] si non pas défini.
     * @var array $auth_users Liste des utilisateurs autorisés à prendre le contrôle d'un autre utlisateur.
     *                             vide par défaut.
     * @var array $allowed_roles Liste des rôles utilisateurs pour lesquels la prise de contrôle est autorisés.
     *                                ['subscriber'] si non pas défini.
     * @var array $allowed_users Liste des utilisateurs pour lesquels la prise de contrôle est permise.
     *                                vide par défaut.
     * }
     */
    public function defaults(): array
    {
        return [
            'auth_roles'    => ['administrator'],
            'auth_users'    => [],
            'allowed_roles' => ['subscriber'],
            'allowed_users' => [],
            'wp_admin'      => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public function eventCan(WP_User $caller, WP_User $called, $event): void
    {
        if ($event->getName() !== 'user_control.can.' . $this->getName()) {
            $this->can = false;
        } else {
            $this->can = $this->can($caller, $called);
        }
    }

    /**
     * @inheritDoc
     */
    public function getAllowedRoleList(): array
    {
        return $this->get('allowed_roles', []);
    }

    /**
     * @inheritDoc
     */
    public function getAuth($action = 'switch'): ?WP_User
    {
        if (!is_user_logged_in()) {
            return null;
        }

        $user = null;
        switch ($action) {
            case 'switch':
                $user = wp_get_current_user();
                break;
            case 'restore' :
                $user = get_userdata($this->_checkCookies());
                break;
        }

        if (!is_a($user, 'WP_User')) {
            // Test d'intégrité de l'utilisateur récupéré
            return null;
        } elseif (!array_intersect($user->roles, $this->get('auth_roles', []))) {
            // Vérification des autorisations pour le rôle de l'utilisateur courant
            return null;
        } elseif ($auth_users = $this->get('auth_users', [])) {
            // Vérification des autorisations parmis la liste des utilisateurs habilités
            $users = [];

            foreach ($auth_users as $auth_user) {
                if (!$user_data = $this->getUserData($auth_user)) {
                    continue;
                }
                $users[] = $user_data;
            }

            if (!in_array($user, $users)) {
                return null;
            }
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getUserData($user): ?WP_User
    {
        if (is_a($user, 'WP_User')) {
            return $user;
        } elseif (is_numeric($user)) {
            return get_userdata((int)$user);
        } elseif (is_string($user)) {
            return get_user_by('login', $user);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function isAllowed($user): bool
    {
        if (!$user = $this->getUserData($user)) {
            return false;
        } elseif (!array_intersect($user->roles, $this->get('allowed_roles', []))) {
            // Vérification des autorisations pour le rôle de l'utilisateur courant
            return false;
        } elseif ($allowed_users = $this->get('allowed_users', [])) {
            // Vérification des autorisations parmis la liste des utilisateurs habilités
            $users = [];

            foreach ($allowed_users as $allowed_user) {
                if (!$user_data = $this->getUserData($allowed_user)) :
                    continue;
                endif;
                $users[] = $user_data;
            }

            if (!in_array($user, $users)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function isAuth($action = 'switch'): bool
    {
        return !!$this->getAuth($action);
    }
}