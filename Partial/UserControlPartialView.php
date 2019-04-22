<?php

namespace tiFy\Plugins\UserControl\Partial;

use BadMethodCallException;
use Exception;
use tiFy\Partial\PartialView;

class UserControlPartialView extends PartialView
{
    /**
     * Délégation d'appel des méthodes de l'instance du gabarit d'affichage associé.
     *
     * @param string $name Nom de la méthode à appeler.
     * @param array $arguments Liste des variables passées en argument.
     *
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, $this->mixins)) {
            try {
                return $this->getEngine()->get('partial')->$name(...$arguments);
            } catch (Exception $e) {
                throw new BadMethodCallException(sprintf(__('La méthode %s n\'est pas disponible.', 'tify'), $name));
            }
        } else {
            return null;
        }
    }
}