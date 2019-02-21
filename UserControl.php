<?php

namespace tiFy\Plugins\UserControl;

use tiFy\Plugins\UserControl\Contracts\UserControlItemHandlerInterface;

/**
 * Class UserControl
 *
 * @desc Extension PresstiFy de prise de controle de compte utilisateur.
 * @author Jordy Manner <jordy@milkcreation.fr>
 * @package tiFy\Plugins\UserControl
 * @version 2.0.8
 */
class UserControl
{
    /**
     * Liste des éléments déclarés.
     * @var UserControlItemHandlerInterface[]
     */
    protected $items = [];

    /**
     * CONSTRUCTEUR.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('init', function () {
            foreach(config('user-control', []) as $name => $attrs) :
                $this->_register($name, $attrs);
            endforeach;
        }, 999999);
    }

    /**
     * Déclaration d'un controleur de prise de contrôle.
     *
     * @param string $name Nom de qualification.
     * @param array $attrs Attributs de configuration.
     *
     * @return null|UserControlItemHandler
     */
    private function _register($name, $attrs = [])
    {
        return $this->items[$name] = app()->get('user-control.handler', [$name, $attrs]);
    }

    /**
     * Ajout d'une déclaration de controleur de prise de contrôle.
     *
     * @param string $name Nom de qualification.
     * @param array $attrs Attributs de configuration.
     *
     * @return $this
     */
    public function add($name, $attrs)
    {
        config()->set("user-control.{$name}", $attrs);

        return $this;
    }

    /**
     * Récupération des classes de rappel de prise de contrôle de compte utilisateur
     *
     * @param string $name Identifiant de qualification
     *
     * @return null|UserControlItemHandlerInterface
     */
    public function get($name)
    {
        return $this->items[$name] ?? null;
    }

    /**
     * Récupération du chemin absolu vers le répertoire des ressources.
     *
     * @param string $path Chemin relatif du sous-repertoire.
     *
     * @return string
     */
    public function resourcesDir($path = '')
    {
        $cinfo = class_info($this);
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists($cinfo->getDirname() . "/Resources{$path}"))
            ? $cinfo->getDirname() . "/Resources{$path}"
            : '';
    }

    /**
     * Récupération de l'url absolue vers le répertoire des ressources.
     *
     * @param string $path Chemin relatif du sous-repertoire.
     *
     * @return string
     */
    public function resourcesUrl($path = '')
    {
        $cinfo = class_info($this);
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists($cinfo->getDirname() . "/Resources{$path}"))
            ? $cinfo->getUrl() . "/Resources{$path}"
            : '';
    }
}
