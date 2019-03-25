<?php

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Partial\PartialView;

class UserControlPartialView extends PartialView
{
    /**
     * Translation d'appel des méthodes de l'application associée.
     *
     * @param string $name Nom de la méthode à appeler.
     * @param array $arguments Liste des variables passées en argument.
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (in_array($name, $this->mixins)) :
            return call_user_func_array(
                [$this->engine->get('partial'), $name],
                $arguments
            );
        endif;

        return null;
    }
}