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
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Template;
use Sowieso\ImageStylingBundle\Image\StyleCalculator;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsHook('parseTemplate', 'onParseTemplate')]
class ParseTemplateListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly Studio $studio,
        private readonly StyleCalculator $styleCalculator,
    ) {
    }

    /**
     * For image and gallery templates, add the styling for the images.
     *
     * @param Template $template
     */
    public function onParseTemplate(Template $template): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        // Do nothing on a backend request
        if (true === $this->scopeMatcher->isBackendRequest($request)) {
            return;
        }

        // Check the name of the template.
        // The style calculation is just important for image-templates
        if (false === str_starts_with($template->getName(), 'image')) {
            return;
        }

        // Check if the current template has an image
        if (!$template->__get('singleSRC')) {
            return;
        }

        // Generate a figure object
        $figure = $this->studio
            ->createFigureBuilder()
            ->from($template->__get('singleSRC'))
            ->setSize($template->__get('size'))
            ->enableLightbox($template->__get('fullsize'))
            // ->setMetadata($this->objModel->getOverwriteMetadata())
            ->buildIfResourceExists()
        ;

        if (null === $figure) {
            return;
        }

        // Calculate the image styling
        $this->styleCalculator->calculate($figure);

        // Get the new generated CSS class for the figure element
        $floatClass = $template->__get('floatClass');
        $template->__set('floatClass', $floatClass . ' ' . $this->styleCalculator->getCssClass());
    }
}
