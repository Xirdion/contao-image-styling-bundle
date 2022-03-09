<?php

declare(strict_types=1);

/*
 * This file is part of SowieSo contao-image-styling-bundle
 *
 * @copyright  Copyright (c) 2022, Ideenwerkstatt Sowieso GmbH & Co. KG
 * @author     Sowieso GmbH & Co. KG <https://sowieso.team>
 * @link       https://github.com/sowieso-web/contao-basic-bundle
 */

namespace Sowieso\ImageStylingBundle\Tests;

use Contao\TestCase\ContaoTestCase;
use Sowieso\BasicBundle\ContaoImageStylingBundle;

class ContaoImageStylingBundleTest extends ContaoTestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new ContaoImageStylingBundle();
        $this->assertInstanceOf(ContaoImageStylingBundle::class, $bundle);
    }
}
