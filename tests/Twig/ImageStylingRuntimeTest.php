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

use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\ImageResult;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\String\HtmlAttributes;
use Contao\TestCase\ContaoTestCase;
use Sowieso\ImageStylingBundle\Image\StyleCalculator;
use Sowieso\ImageStylingBundle\Twig\ImageStylingRuntime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ImageStylingRuntimeTest extends ContaoTestCase
{
    private ?Request $request;
    private bool $isFrontend;
    private RequestStack $requestStack;
    private ScopeMatcher $scopeMatcher;
    private StyleCalculator $styleCalculator;

    protected function setUp(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack
            ->method('getCurrentRequest')
            ->willReturnCallback(\Closure::bind(fn () => $this->request, $this))
        ;
        $this->requestStack = $requestStack;

        $scopeMatcher = $this->createMock(ScopeMatcher::class);
        $scopeMatcher
            ->method('isFrontendRequest')
            ->willReturnCallback(\Closure::bind(fn () => $this->isFrontend, $this))
        ;
        $this->scopeMatcher = $scopeMatcher;

        $styleCalculator = $this->createMock(StyleCalculator::class);
        $styleCalculator
            ->method('getCssClass')
            ->willReturn('image_container--1')
        ;
        $this->styleCalculator = $styleCalculator;
    }

    /**
     * @dataProvider getData
     *
     * @param Figure|null  $figure
     * @param Request|null $request
     * @param bool         $scope
     * @param string       $htmlAttributes
     *
     * @return void
     */
    public function testCalculateStyling(?Figure $figure, ?Request $request, bool $scope, string $htmlAttributes): void
    {
        $this->request = $request;
        $this->isFrontend = $scope;

        $runtime = new ImageStylingRuntime($this->requestStack, $this->scopeMatcher, $this->styleCalculator);

        $attributes = $runtime->calculateStyling($figure);

        $this->assertInstanceOf(HtmlAttributes::class, $attributes);
        $this->assertSame($htmlAttributes, $attributes->__toString());
    }

    /**
     * @return \Generator
     */
    private function getData(): \Generator
    {
        $figure = new Figure($this->createMock(ImageResult::class));
        $request = $this->createMock(Request::class);

        yield [null, null, false, ''];
        yield [$figure, null, false, ''];
        yield [$figure, $request, false, ''];
        yield [$figure, $request, true, ' class="image_container--1"'];
    }
}
