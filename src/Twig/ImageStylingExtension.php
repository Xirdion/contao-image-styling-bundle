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

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageStylingExtension extends AbstractExtension
{
    /**
     * Adding new twig function "img_style".
     * This function will calculate extra styling for Contao\CoreBundle\Image\Studio\Figure elements.
     *
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('img_style', [ImageStylingRuntime::class, 'calculateStyling']),
        ];
    }
}
