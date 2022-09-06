<?php

declare(strict_types=1);

/*
 * This file is part of SowieSo contao-image-styling-bundle
 *
 * @copyright  Copyright (c) 2022, Ideenwerkstatt Sowieso GmbH & Co. KG
 * @author     Sowieso GmbH & Co. KG <https://sowieso.team>
 * @link       https://github.com/sowieso-web/contao-image-styling-bundle
 */

namespace Sowieso\ImageStylingBundle\Tests\DependencyInjection;

use Contao\TestCase\ContaoTestCase;
use Sowieso\ImageStylingBundle\DependencyInjection\ContaoImageStylingExtension;
use Sowieso\ImageStylingBundle\EventListener\GeneratePageListener;
use Sowieso\ImageStylingBundle\EventListener\ParseTemplateListener;
use Sowieso\ImageStylingBundle\Image\StyleCalculator;
use Sowieso\ImageStylingBundle\Twig\ImageStylingExtension;
use Sowieso\ImageStylingBundle\Twig\ImageStylingRuntime;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ContaoImageStylingExtensionTest extends ContaoTestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->container = $this->getContainerWithContaoConfiguration();

        $extension = new ContaoImageStylingExtension();
        $extension->load([], $this->container);
    }

    public function testRegisterServices(): void
    {
        $this->assertTrue($this->container->has(StyleCalculator::class));
        $this->assertTrue($this->container->has(ImageStylingRuntime::class));
        $this->assertTrue($this->container->has(ImageStylingExtension::class));
    }

    public function testRegisterListener(): void
    {
        $this->assertTrue($this->container->has(ParseTemplateListener::class));
        $this->assertTrue($this->container->has(GeneratePageListener::class));
    }
}
