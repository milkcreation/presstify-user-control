<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl;

use tiFy\Plugins\UserControl\Contracts\{
    UserControl as UserControlContract,
    UserControlFactory as UserControlFactoryContract
};

/**
 * @desc Extension PresstiFy de prise de controle de compte utilisateur.
 * @author Jordy Manner <jordy@milkcreation.fr>
 * @package tiFy\Plugins\UserControl
 * @version 2.0.24
 */
class UserControl implements UserControlContract
{
    /**
     * Liste des éléments déclarés.
     * @var UserControlFactoryContract[]
     */
    protected $items = [];

    /**
     * @inheritDoc
     */
    public function get(string $name): ?UserControlFactoryContract
    {
        return $this->items[$name] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function register(string $name, array $attrs = []): UserControlFactoryContract
    {
        return $this->items[$name] = new UserControlFactory($name, $attrs, $this);
    }

    /**
     * @inheritDoc
     */
    public function resourcesDir($path = ''): string
    {
        $path = $path ? '/' . ltrim($path, '/') : '';

        return file_exists(__DIR__ . "/Resources{$path}") ? __DIR__ . "/Resources{$path}" : '';
    }

    /**
     * @inheritDoc
     */
    public function resourcesUrl($path = ''): string
    {
        $path = $path ? '/' . ltrim($path, '/') : '';

        return file_exists(__DIR__ . "/Resources{$path}") ? class_info($this)->getUrl() . "/Resources{$path}" : '';
    }
}
