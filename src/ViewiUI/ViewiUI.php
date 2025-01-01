<?php

namespace Viewi\UI;

use Viewi\Icons\ViewiIcons;
use Viewi\Packages\ViewiPackage;

class ViewiUI extends ViewiPackage
{
    public static function getComponentsPath(): ?string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'Components';
    }

    public static function jsDir(): ?string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'js';
    }

    public static function jsModulePackagePath(): ?string
    {
        return 'viewi-ui';
    }

    public static function name(): string
    {
        return 'viewi-ui';
    }

    public static function getDependencies(): array
    {
        return [ViewiIcons::class];
    }
}
