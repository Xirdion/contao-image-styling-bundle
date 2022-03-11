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
    /** @var string[] */
    private static array $css = [];

    private ?Request $request;
    private Template $template;
    private string $style;
    private string $mediaStyle;

    public function __construct(
        private ScopeMatcher $scopeMatcher,
        RequestStack $requestStack,
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

        // Check if the template has an ID (inserted image templates do not have one)
        if (null === $template->__get('id')) {
            return;
        }

        $this->style = <<<'CSS'
            .image_container_%s::before {
                padding-top: %s%%;
            }
            .image_container_%s {
                width: %spx;
            }
            CSS;

        $this->mediaStyle = <<<'CSS'
            @media only screen and %s {
                .image_container_%s::before {
                    padding-top: %s%%;
                }
                .image_container_%s {
                    width: %spx;
                }
            }
            CSS;

        $this->template = $template;

        // Check if it is an image template
        if (str_starts_with($template->getName(), 'image')) {
            $this->handleImageTemplate();
        }

        // Check if it is a gallery template
        if (str_starts_with($template->getName(), 'gallery_')) {
            $this->calculateGalleryStyling();
        }
    }

    /**
     * Handle image templates to modify the template data.
     *
     * @return void
     */
    private function handleImageTemplate(): void
    {
        // Generate the additional CSS class for the image container
        $picture = (array) $this->template->__get('picture');
        $cssClass = $this->generateCssClass($picture);

        // Add the additional CSS class to the template
        $floatClass = (string) $this->template->__get('floatClass');
        $this->template->__set('floatClass', $floatClass . ' ' . $cssClass);
    }

    /**
     * Handle gallery templates to modify the template data.
     */
    private function calculateGalleryStyling(): void
    {
        $i = 0;
        $rows = (array) $this->template->__get('body');
        foreach ($rows as $rowI => $row) {
            /** @var \stdClass $col */
            foreach ($row as $colI => $col) {
                // check if an image should get rendered
                if (false === $col->addImage) {
                    continue;
                }

                // Generate the additional CSS class per gallery image
                $cssClass = $this->generateCssClass((array) $col->picture, '_' . ++$i);

                // Add the additional CSS class to the image template
                if (false === isset($col->floatClass)) {
                    $col->floatClass = '';
                }
                $col->floatClass .= ' ' . $cssClass;
                $rows[$rowI][$colI] = $col;
            }
        }

        // Update the template data
        $this->template->__set('body', $rows);
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
    private function generateCssClass(array $picture, string $idSuffix = ''): string
    {
        // prepare container class
        $cssClass = 'image_container_' . $this->template->__get('id') . '_' . $this->template->__get('type') . $idSuffix;

        // check the file type of the image
        $ext = pathinfo($picture['img']['src'])['extension'] ?? '';

        // add the extension to the container class
        $cssClass .= ' image_container_' . $ext;

        // check if there are any values for some styles
        if (0 === \count($picture['sources']) || (!$picture['sources']['0']['width'] && !$picture['img']['width'])) {
            return $cssClass;
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

        return $cssClass;
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
        $id = $this->template->__get('id') . '_' . $this->template->__get('type') . $idSuffix;

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
