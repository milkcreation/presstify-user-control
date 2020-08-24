<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Partial;

use tiFy\Partial\PartialDriver as BasePartialDriver;
use tiFy\Plugins\UserControl\Contracts\{AbstractPartialDriver as AbstractPartialDriverContract, UserControl};

abstract class AbstractPartialDriver extends BasePartialDriver implements AbstractPartialDriverContract
{
    /**
     * Instance du gestionnaire de plugin.
     * @var UserControl|null
     */
    protected $userControl;

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
        parent::boot();

        $this->set(
            'viewer.directory', $this->userControl->resourcesDir('views') . '/' . class_info($this)->getKebabName()
        );
    }

    /**
     * @inheritDoc
     */
    public function setUserControl(UserControl $userControl): AbstractPartialDriverContract
    {
        $this->userControl = $userControl;

        return $this;
    }
}