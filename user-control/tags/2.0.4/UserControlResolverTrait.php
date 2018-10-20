<?php

namespace tiFy\Plugins\UserControl;

trait UserControlResolverTrait
{
    /**
     * Instance du controleur principal.
     *
     * @return UserControl
     */
    public function uc()
    {
        return app(UserControl::class);
    }
}
