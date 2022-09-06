<?php

declare(strict_types=1);

/*
 * This file is part of SowieSo contao-image-styling-bundle
 *
 * @copyright  Copyright (c) 2022, Ideenwerkstatt Sowieso GmbH & Co. KG
 * @author     Sowieso GmbH & Co. KG <https://sowieso.team>
 * @link       https://github.com/sowieso-web/contao-image-styling-bundle
 */

namespace Sowieso\ImageStylingBundle\Tests\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\TestCase\ContaoTestCase;
use Sowieso\ImageStylingBundle\ContaoImageStylingBundle;
use Sowieso\ImageStylingBundle\ContaoManager\Plugin;

class PluginTest extends ContaoTestCase
{
    public function testGetBundles(): void
    {
        $parser = $this->createMock(ParserInterface::class);

        $plugin = new Plugin();
        $bundles = $plugin->getBundles($parser);

        $this->assertCount(1, $bundles);

        $bundle = $bundles[0];
        $this->assertSame(ContaoImageStylingBundle::class, $bundle->getName());
        $this->assertIsArray($bundle->getLoadAfter());
        $this->assertSame(ContaoCoreBundle::class, $bundle->getLoadAfter()[0]);
    }
}
