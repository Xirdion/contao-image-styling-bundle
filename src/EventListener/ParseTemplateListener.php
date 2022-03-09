<?php

declare(strict_types=1);

/*
 * This file is part of SowieSo contao-image-styling-bundle
 *
 * @copyright  Copyright (c) 2022, Ideenwerkstatt Sowieso GmbH & Co. KG
 * @author     Sowieso GmbH & Co. KG <https://sowieso.team>
 * @link       https://github.com/sowieso-web/contao-basic-bundle
 */

namespace Sowieso\ImageStylingBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsHook('parseTemplate', 'onParseTemplate')]
class ParseTemplateListener
{
    /** @var array<string> */
    private static array $css = [];

    private ?Request $request;
    private Template $template;

    /** @phpstan-ignore-next-line */
    private array $templateData;

    /**
     * Styling for normal images.
     *
     * @var string
     */
    private string $style = <<<'CSS'
        .image_container_%s::before {
            padding-top: %s%%;
        }
        .image_container_%s {
            width: %spx;
        }
        CSS;

    /**
     * Styling of images with multiple sources.
     *
     * @var string
     */
    private string $mediaStyle = <<<'CSS'
        @media only screen and %s {
            .image_container_%s::before {
                padding-top: %s%%;
            }
            .image_container_%s {
                width: %spx;
            }
        }
        CSS;

    public function __construct(
        private ScopeMatcher $scopeMatcher,
        RequestStack $requestStack
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * For image and gallery templates, add the styling for the images.
     *
     * @param Template $template
     */
    public function onParseTemplate(Template $template): void
    {
        if (null === $this->request) {
            return;
        }

        // Do nothing on a backend request
        if (true === $this->scopeMatcher->isBackendRequest($this->request)) {
            return;
        }

        $this->template = $template;
        $this->templateData = $this->template->getData();

        // Check if it is an image template
        if (str_starts_with($template->getName(), 'image')) {
            $containerClass = $this->calculateStyling((array) $this->templateData['picture']);

            // add container class to the template
            $this->templateData['containerClass'] = $containerClass;
            $this->template->setData($this->templateData);
        }

        // Check if it is a gallery template
        if (str_starts_with($template->getName(), 'gallery_')) {
            $this->calculateGalleryStyling();
        }
    }

    /**
     * Get the picture values for a gallery template.
     */
    private function calculateGalleryStyling(): void
    {
        $i = 0;
        $body = $this->templateData['body'];
        foreach ($body as $row) {
            foreach ($row as $col) {
                // check if an image should get rendered
                if (!$col->addImage) {
                    continue;
                }

                ++$i;

                $containerClass = $this->calculateStyling((array) $col->picture, '_' . $i);

                // add container class to the template
                // data is automatically added to $this->template as arrays are used here
                $col->containerClass = $containerClass;
            }
        }
    }

    /**
     * Check which properties must be used to calculate the styling.
     *
     * @param array  $picture
     * @param string $idSuffix
     *
     * @return string
     *
     * @phpstan-ignore-next-line
     */
    private function calculateStyling(array $picture, string $idSuffix = ''): string
    {
        // prepare container class
        $containerClass = 'image_container_' . ($this->templateData['id'] ?? '') . '_' . ($this->templateData['type'] ?? '') . $idSuffix;

        // check the file type of the image
        $ext = pathinfo($picture['img']['src'])['extension'] ?? '';

        // add the extension to the container class
        $containerClass .= ' image_container_' . $ext;

        // check if there are any values for some styles
        if (0 === \count($picture['sources']) || (!$picture['sources']['0']['width'] && !$picture['img']['width'])) {
            // add class for no lazy loading
            $containerClass .= ' image_container_nolazy';

            return $containerClass;
        }

        // check if the image sources or the image itself should be used
        if ($picture['sources']) {
            foreach ($picture['sources'] as $source) {
                // Check if it is styling with media query
                if (false === isset($source['media'])) {
                    continue;
                }
                $this->addStyling((int) $source['width'], (int) $source['height'], $idSuffix, (string) $source['media']);
            }
        } elseif ($picture['img']) {
            $this->addStyling((int) $picture['img']['width'], (int) $picture['img']['height'], $idSuffix);
        }

        // If the template has no lazy load, add the additional class
        if (true === str_contains($this->template->getName(), 'nolazy')) {
            $containerClass .= ' image_container_nolazy';
        }

        return $containerClass;
    }

    /**
     * Try to calculate the padding and add the style to template.
     *
     * @param int    $width
     * @param int    $height
     * @param string $idSuffix
     * @param string $media
     */
    private function addStyling(int $width, int $height, string $idSuffix, string $media = ''): void
    {
        // check if the height is not 0
        if (0 === $height) {
            return;
        }

        // generate the style id
        $id = ($this->templateData['id'] ?? '') . '_' . ($this->templateData['type'] ?? '') . $idSuffix;

        // calculate the padding
        $padding = round(100 / ($width / $height), 2);

        if ($media) {
            $style = sprintf($this->mediaStyle, $media, $id, $padding, $id, $width);
        } else {
            $style = sprintf($this->style, $id, $padding, $id, $width);
        }

        // add the styling to the collection
        self::$css[] = $style;

        // generate the styling tag for the head
        $GLOBALS['TL_HEAD']['resp_image_style'] = Template::generateInlineStyle(implode(\PHP_EOL, self::$css));
    }
}
