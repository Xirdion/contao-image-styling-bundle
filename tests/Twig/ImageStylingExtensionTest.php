<?php

declare(strict_types=1);

/*
 * This file is part of SowieSo contao-image-styling-bundle
 *
 * @copyright  Copyright (c) 2022, Ideenwerkstatt Sowieso GmbH & Co. KG
 * @author     Sowieso GmbH & Co. KG <https://sowieso.team>
 * @link       https://github.com/sowieso-web/contao-image-styling-bundle
 */

namespace Sowieso\ImageStylingBundle\Tests\Twig;

use Contao\TestCase\ContaoTestCase;
use Sowieso\ImageStylingBundle\Twig\ImageStylingExtension;

class ImageStylingExtensionTest extends ContaoTestCase
{
    public function testGetFunctions(): void
    {
        $extension = new ImageStylingExtension();
        $functions = $extension->getFunctions();

        $this->assertIsArray($functions);
        $this->assertCount(1, $functions);

        $function = $functions[0];
        $this->assertSame('img_style', $function->getName());
    }
}
