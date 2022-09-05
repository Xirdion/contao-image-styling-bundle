<?php

declare(strict_types=1);

/*
 * This file is part of SowieSo contao-image-styling-bundle
 *
 * @copyright  Copyright (c) 2022, Ideenwerkstatt Sowieso GmbH & Co. KG
 * @author     Sowieso GmbH & Co. KG <https://sowieso.team>
 * @link       https://github.com/sowieso-web/contao-image-styling-bundle
 */

namespace Sowieso\ImageStylingBundle\Twig;

use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\String\HtmlAttributes;
use Sowieso\ImageStylingBundle\Image\StyleCalculator;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\RuntimeExtensionInterface;

class ImageStylingRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly StyleCalculator $styleCalculator
    ) {
    }

    /**
     * Runtime for the twig function "img_style".
     * Adding an extra class to the figure element.
     *
     * @param Figure|null $figure
     *
     * @return HtmlAttributes|string
     */
    public function calculateStyling(Figure|null $figure): HtmlAttributes|string
    {
        // Initialize the HtmlAttributes
        $attributes = class_exists(HtmlAttributes::class) ? new HtmlAttributes() : '';

        // First check if the image styling should get calculated
        if (null === $figure) {
            return $attributes;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return $attributes;
        }

        if (false === $this->scopeMatcher->isFrontendRequest($request)) {
            return $attributes;
        }

        // Calculate the styling and add the new CSS class to the figure
        $this->styleCalculator->calculate($figure);

        if ($attributes instanceof HtmlAttributes) {
            $attributes->set('class', $this->styleCalculator->getCssClass());
        } else {
            $attributes = 'class="' . $this->styleCalculator->getCssClass() . '"';
        }

        return $attributes;
    }
}
