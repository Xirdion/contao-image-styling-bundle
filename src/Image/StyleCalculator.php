<?php

declare(strict_types=1);

/*
 * This file is part of SowieSo contao-image-styling-bundle
 *
 * @copyright  Copyright (c) 2022, Ideenwerkstatt Sowieso GmbH & Co. KG
 * @author     Sowieso GmbH & Co. KG <https://sowieso.team>
 * @link       https://github.com/sowieso-web/contao-image-styling-bundle
 */

namespace Sowieso\ImageStylingBundle\Image;

use Contao\CoreBundle\Image\Studio\Figure;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class StyleCalculator
{
    /**
     * Internal counter to generate unique CSS classes for the figure elements.
     *
     * @var int
     */
    private static int $counter = 0;

    /**
     * Style markup for normal images.
     *
     * @var string
     */
    private static string $style = <<<'CSS'
        .%s::before {padding-top: %s%%;}
        .%s {width: %spx;}
        CSS;

    /**
     * Style markup for images with image sizes.
     *
     * @var string
     */
    private static string $mediaStyle = <<<'CSS'
        @media only screen and %s {
            .%s::before {padding-top: %s%%;}
            .%s {width: %spx;}
        }
        CSS;

    /**
     * Variable to hold the current css class for the figure element.
     *
     * @var string
     */
    private string $cssClass;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly Filesystem $filesystem,
    ) {
    }

    /**
     * Check if any styling has been generated.
     *
     * @return bool
     */
    public function hasStyling(): bool
    {
        return 0 !== self::$counter;
    }

    /**
     * Get the CSS file which is used to add the generated styles to html.
     *
     * @param bool $relative
     *
     * @return string
     */
    public function getStyleFile(bool $relative = false): string
    {
        $projectDir = $this->kernel->getProjectDir();

        // Generate absolute path to custom CSS file
        $path = $projectDir . '/public/bundles/contaoimagestyling/';

        if (true === $relative) {
            // Create a relative path from the project directory
            $path = $this->filesystem->makePathRelative($path, $projectDir);
        }

        return $path . 'image_style.css';
    }

    /**
     * Return the current generated CSS class.
     * This can be used to add the class to a html element.
     *
     * @return string
     */
    public function getCssClass(): string
    {
        return $this->cssClass;
    }

    /**
     * Walk through the properties of the given figure to calculate extra styling for the image.
     *
     * @param Figure $figure
     *
     * @return void
     */
    public function calculate(Figure $figure): void
    {
        // First remove all old styling
        if (0 === self::$counter) {
            $this->filesystem->dumpFile($this->getStyleFile(), '');
        }

        // Generate an unique CSS class
        $this->cssClass = 'image_container--' . ++self::$counter;

        // Create and initialize the styling variable
        $styling = '';

        /** @var array{array{srcset:string, src:string, width:int, height:int, media:string}} $sources */
        $sources = $figure->getImage()->getSources();

        // Check if an image size width different media queries is used
        // If there are no entries the height and width of the picture can be used
        // to calculate the styling
        if (0 === \count($sources)) {
            /** @var array{srcset:string, src:string, width:int, height:int, class:string, hasSingleAspectRatio:bool} $img */
            $img = $figure->getImage()->getImg();

            $styling = $this->createStyling($img['width'], $img['height']);
        } else {
            // Flag to avoid wrong styling for images with different media sizes
            // It is only set true if the picture sources have media fields
            // If the flag is true a simple styling without media query is not possible
            $hasMedia = false;

            // Loop over all available media sources and calculate the styling
            foreach ($sources as $source) {
                if (false === isset($source['media'])) {
                    if (true === $hasMedia) {
                        continue;
                    }

                    $styling = $this->createStyling($source['width'], $source['height']);
                } else {
                    $hasMedia = true;
                    $styling = $this->createStyling($source['width'], $source['height'], $source['media']);
                }
            }
        }

        // Try to add the generated Styling to a file
        // If no style was generated just to nothing
        if ('' !== $styling) {
            $this->filesystem->appendToFile($this->getStyleFile(), $styling);
        }
    }

    /**
     * Calculate the image padding and create the correct styling for the image.
     *
     * @param int    $width
     * @param int    $height
     * @param string $media
     *
     * @return string
     */
    private function createStyling(int $width, int $height, string $media = ''): string
    {
        // If the height is 0 no styling can be calculated
        if (0 === $height) {
            return '';
        }

        // Calculate the padding
        $padding = round(100 / ($width / $height), 2);

        if ('' === $media) {
            return sprintf(self::$style, $this->cssClass, $padding, $this->cssClass, $width);
        }

        return sprintf(self::$mediaStyle, $media, $this->cssClass, $padding, $this->cssClass, $width);
    }
}
