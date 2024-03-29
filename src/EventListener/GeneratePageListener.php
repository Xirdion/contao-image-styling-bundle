<?php

declare(strict_types=1);

/*
 * This file is part of SowieSo contao-image-styling-bundle
 *
 * @copyright  Copyright (c) 2022, Ideenwerkstatt Sowieso GmbH & Co. KG
 * @author     Sowieso GmbH & Co. KG <https://sowieso.team>
 * @link       https://github.com/sowieso-web/contao-image-styling-bundle
 */

namespace Sowieso\ImageStylingBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Sowieso\ImageStylingBundle\Image\StyleCalculator;

#[AsHook('generatePage')]
class GeneratePageListener
{
    public function __construct(
        private readonly StyleCalculator $styleCalculator,
    ) {
    }

    /**
     * If some image styling was calculated include the given CSS file.
     *
     * @param PageModel   $pageModel
     * @param LayoutModel $layoutModel
     * @param PageRegular $pageRegular
     *
     * @return void
     */
    public function __invoke(PageModel $pageModel, LayoutModel $layoutModel, PageRegular $pageRegular): void
    {
        if ($this->styleCalculator->hasStyling()) {
            $GLOBALS['TL_CSS']['imageStyling'] = $this->styleCalculator->getStyleFile(true) . '|static';
        }
    }
}
