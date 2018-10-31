<?php

namespace tiFy\Plugins\UserControl\Contracts;

use tiFy\Contracts\Partial\PartialController;

interface UserControlPartialController extends PartialController
{
    /**
     * Récupération du chemin absolu vers le répertoire des ressources.
     *
     * @param string $path Chemin relatif du sous-repertoire.
     *
     * @return string
     */
    public function resourcesDir($path = '');

    /**
     * Récupération de l'url absolue vers le répertoire des ressources.
     *
     * @param string $path Chemin relatif du sous-repertoire.
     *
     * @return string
     */
    public function resourcesUrl($path = '');
}