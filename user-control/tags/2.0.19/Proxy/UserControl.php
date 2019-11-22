<?php declare(strict_types=1);

namespace tiFy\Plugins\UserControl\Proxy;

use tiFy\Support\Proxy\AbstractProxy;

/**
 * @method static string get(string $name)
 * @method static string register(string $name, array $attrs = [])
 */
class UserControl extends AbstractProxy
{
    public static function getInstanceIdentifier()
    {
        return 'user-control';
    }
}