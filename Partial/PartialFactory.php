<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Partial\{PartialFactory as BasePartialFactory, PartialView};
use tiFy\Plugins\UserControl\Contracts\{PartialFactory as PartialFactoryContract, UserControl};

abstract class PartialFactory extends BasePartialFactory implements PartialFactoryContract
{
    /**
     * Instance du gestionnaire de plugin.
     * @var UserControl|null
     */
    protected $userControl;

    /**
     * @inheritDoc
     */
    public function viewer($view = null, $data = [])
    {
        if (!$this->viewer) {
            $default_dir = $this->userControl->resourcesDir('views');
            $this->viewer = view()
                ->setDirectory($default_dir)
                ->setController(PartialView::class)
                ->setOverrideDir(
                    (($override_dir = $this->get('viewer.override_dir')) && is_dir($override_dir))
                        ? $override_dir
                        : $default_dir
                )
                ->set('partial', $this);
        }

        if (func_num_args() === 0) {
            return $this->viewer;
        }

        return $this->viewer->make("_override::{$view}", $data);
    }

    /**
     * @inheritDoc
     */
    public function setUserControl(UserControl $userControl): PartialFactoryContract
    {
        $this->userControl = $userControl;

        return $this;
    }
}