<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Contracts;

use tiFy\Contracts\Partial\PartialDriver as BasePartialDriver;

interface AbstractPartialDriver extends BasePartialDriver
{
    /**
     * Définition de l'instance du gestionnaire de plugin
     *
     * @param UserControl $userControl
     *
     * @return $this
     */
    public function setUserControl(UserControl $userControl): AbstractPartialDriver;
}