<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Contracts;

interface UserControl
{
    /**
     * Récupération des classes de rappel de prise de contrôle de compte utilisateur
     *
     * @param string $name Identifiant de qualification
     *
     * @return UserControlFactory|null
     */
    public function get(string $name): ?UserControlFactory;

    /**
     * Déclaration d'un controleur de prise de contrôle.
     *
     * @param string $name Nom de qualification.
     * @param array $attrs Attributs de configuration.
     *
     * @return UserControlFactory|null
     */
    public function register(string $name, array $attrs = []): ?UserControlFactory;

    /**
     * Récupération du chemin absolu vers le répertoire des ressources.
     *
     * @param string $path Chemin relatif du sous-repertoire.
     *
     * @return string
     */
    public function resourcesDir($path = ''): string;

    /**
     * Récupération de l'url absolue vers le répertoire des ressources.
     *
     * @param string $path Chemin relatif du sous-repertoire.
     *
     * @return string
     */
    public function resourcesUrl($path = ''): string;
}
