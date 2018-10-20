<?php

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Contracts\Views\ViewInterface;
use tiFy\Partial\PartialController;
use tiFy\Plugins\UserControl\UserControlResolverTrait;
use tiFy\Plugins\UserControl\Contracts\UserControlPartialController;

class AbstractUserControlPartialItem extends PartialController implements UserControlPartialController
{
    use UserControlResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function resourcesDir($path = '')
    {
        return $this->uc()->resourcesDir($path);
    }

    /**
     * {@inheritdoc}
     */
    public function resourcesUrl($path = '')
    {
        return $this->uc()->resourcesUrl($path);
    }

    /**
     * {@inheritdoc}
     */
    public function viewer($view = null, $data = [])
    {
        if (!$this->viewer) :
            $default_dir = $this->resourcesDir('views');
            $this->viewer = view()
                ->setDirectory($default_dir)
                ->setController(UserControlPartialView::class)
                ->setOverrideDir(
                    (($override_dir = $this->get('viewer.override_dir')) && is_dir($override_dir))
                        ? $override_dir
                        : $default_dir
                )
                ->set('partial', $this);
        endif;

        if (func_num_args() === 0) :
            return $this->viewer;
        endif;

        return $this->viewer->make("_override::{$view}", $data);
    }
}