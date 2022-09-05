<?php

declare(strict_types=1);

/*
 * This file is part of SowieSo contao-image-styling-bundle
 *
 * @copyright  Copyright (c) 2022, Ideenwerkstatt Sowieso GmbH & Co. KG
 * @author     Sowieso GmbH & Co. KG <https://sowieso.team>
 * @link       https://github.com/sowieso-web/contao-image-styling-bundle
 */

namespace Sowieso\ImageStylingBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Sowieso\ImageStylingBundle\ContaoImageStylingBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * @param ParserInterface $parser
     *
     * @return array<ConfigInterface>
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoImageStylingBundle::class)
            ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
