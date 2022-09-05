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

use Contao\CoreBundle\Asset\ContaoContext;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Sowieso\ImageStylingBundle\Image\StyleCalculator;

#[AsHook('generatePage')]
class GeneratePageListener
{
    public function __construct(
        private readonly ContaoContext $contaoContext,
        private readonly StyleCalculator $styleCalculator,
    ) {
    }

    public function __invoke(PageModel $pageModel, LayoutModel $layoutModel, PageRegular $pageRegular): void
    {
        $test = $this->contaoContext->getBasePath();
        $test2 = $this->contaoContext->getStaticUrl();

        $GLOBALS['TL_CSS']['imageStyling'] = $this->styleCalculator->getStyleFile(true) . '|static';
    }
}
