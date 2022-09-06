<?php

declare(strict_types=1);

/*
 * This file is part of SowieSo contao-image-styling-bundle
 *
 * @copyright  Copyright (c) 2022, Ideenwerkstatt Sowieso GmbH & Co. KG
 * @author     Sowieso GmbH & Co. KG <https://sowieso.team>
 * @link       https://github.com/sowieso-web/contao-image-styling-bundle
 */

namespace Sowieso\ImageStylingBundle\Tests\Image;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\TestCase\ContaoTestCase;
use Contao\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use Sowieso\ImageStylingBundle\Image\StyleCalculator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

class StyleCalculatorTest extends ContaoTestCase
{
    private ContainerBuilder $container;
    private Studio|MockObject $studio;
    private StyleCalculator $calculator;
    private string $fixturesDir;

    protected function setUp(): void
    {
        $projectDir = (string) realpath(__DIR__ . '/../..');

        $fileSystem = new Filesystem();

        $adapters = [
            Validator::class => new Adapter(Validator::class),
        ];

        $container = $this->getContainerWithContaoConfiguration($projectDir);
        $container->set('filesystem', $fileSystem);
        $framework = $this->mockContaoFramework($adapters);
        $framework->setContainer($container);
        $container->set('contao.framework', $framework);

        $this->container = $container;

        $this->studio = new Studio($container, $projectDir, $projectDir, ['jpg']);

        $this->calculator = new StyleCalculator($fileSystem, $container->getParameter('contao.web_dir'));

        $this->fixturesDir = realpath(__DIR__ . '/..') . '/Fixtures';
    }

    public function testGetStyleFile(): void
    {
        $path = $this->calculator->getStyleFile();

        $expected = $this->container->getParameter('contao.web_dir') . '/bundles/contaoimagestyling/image_style.css';

        $this->assertSame($expected, $path);
    }

    public function testGetStyleFileRelative(): void
    {
        $path = $this->calculator->getStyleFile(true);

        $expected = 'bundles/contaoimagestyling/image_style.css';

        $this->assertSame($expected, $path);
    }

    public function testHasStyling(): void
    {
        $this->assertFalse($this->calculator->hasStyling());
    }

    /**
     * @dataProvider getFigureData
     *
     * @param array{int, int, string}|null $size
     * @param int $counter
     *
     * @return void
     */
    public function testCalculate(array|null $size, int $counter): void
    {
        $singleSRC = $this->fixturesDir . '/example.jpg';

        $figure = $this->studio
            ->createFigureBuilder()
            ->from($singleSRC)
            ->setSize($size)
            ->buildIfResourceExists()
        ;

        if (null === $figure) {
            $this->addWarning('Picture for "' . $singleSRC . '" could not get created!');

            return;
        }

        $this->calculator->calculate($figure);

        $this->assertSame('image_container--' . $counter, $this->calculator->getCssClass());
        $this->assertTrue($this->calculator->hasStyling());
    }

    /**
     * @return \Generator
     */
    private function getFigureData(): \Generator
    {
        yield [null, 1];
        yield [[200, 200, 'crop'], 2];
    }
}
