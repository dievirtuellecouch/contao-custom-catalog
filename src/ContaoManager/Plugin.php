<?php

namespace DVC\ContaoCustomCatalog\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use DVC\ContaoCustomCatalog\DvcContaoCustomCatalogBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(DvcContaoCustomCatalogBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}

