<?php

use Viewi\AppConfig;
use Viewi\UI\ViewiUI;

$d = DIRECTORY_SEPARATOR;
$viewiAppPath = __DIR__ . $d;
$componentsPath =  $viewiAppPath . 'Components';
$buildPath = $viewiAppPath . 'build';
$jsPath = $viewiAppPath . 'js';
$assetsSourcePath = $viewiAppPath . 'assets';
$publicPath = __DIR__ . '/../';
$assetsPublicUrl = '';

return (new AppConfig())
    ->use(ViewiUI::class)
    ->buildTo($buildPath)
    ->buildFrom($componentsPath)
    ->withJsEntry($jsPath)
    ->putAssetsTo($publicPath)
    ->assetsPublicUrl($assetsPublicUrl)
    ->withAssets($assetsSourcePath)
    // ->combine()
    ->developmentMode(true)
    ->buildJsSourceCode()
    ->watchWithNPM(true);
