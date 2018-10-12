<?php

namespace tiFy\Plugins\UserControl\Contracts;

use tiFy\Contracts\Partial\PartialItemInterface;

interface UserControlPartialItemInterface extends PartialItemInterface
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