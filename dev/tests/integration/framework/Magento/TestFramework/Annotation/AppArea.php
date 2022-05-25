<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Annotation;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Application;
use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AppArea
{
    public const ANNOTATION_NAME = 'magentoAppArea';

    /**
     * @var Application
     */
    private $_application;

    /**
     * List of allowed areas.
     *
     * @var array
     */
    private $_allowedAreas = [
        \Magento\Framework\App\Area::AREA_GLOBAL,
        \Magento\Framework\App\Area::AREA_ADMINHTML,
        \Magento\Framework\App\Area::AREA_FRONTEND,
        \Magento\Framework\App\Area::AREA_WEBAPI_REST,
        \Magento\Framework\App\Area::AREA_WEBAPI_SOAP,
        \Magento\Framework\App\Area::AREA_CRONTAB,
        \Magento\Framework\App\Area::AREA_GRAPHQL
    ];

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->_application = $application;
    }

    /**
     * Get current application area
     *
     * @param array $annotations
     * @return string
     * @throws LocalizedException
     */
    protected function _getTestAppArea($annotations)
    {
        $area = isset(
            $annotations['method'][self::ANNOTATION_NAME]
        ) ? current(
            $annotations['method'][self::ANNOTATION_NAME]
        ) : (isset(
            $annotations['class'][self::ANNOTATION_NAME]
        ) ? current(
            $annotations['class'][self::ANNOTATION_NAME]
        ) : \Magento\TestFramework\Application::DEFAULT_APP_AREA);

        if (false == in_array($area, $this->_allowedAreas)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Invalid "@magentoAppArea" annotation, can be "%1" only.',
                    implode('", "', $this->_allowedAreas)
                )
            );
        }

        return $area;
    }

    /**
     * Start test case event observer
     *
     * @param TestCase $test
     */
    public function startTest(TestCase $test)
    {
        $area = $this->getArea($test);

        if (!in_array($area, $this->_allowedAreas, true)) {
            throw new LocalizedException(
                __(
                    'Invalid "@magentoAppArea" annotation, can be "%1" only.',
                    implode('", "', $this->_allowedAreas)
                )
            );
        }

        if ($this->_application->getArea() !== $area) {
            $this->_application->reinitialize();

            if ($this->_application->getArea() !== $area) {
                $this->_application->loadArea($area);
            }
        }
    }

    /**
     * Get the configured application area
     *
     * @param TestCase $test
     * @return string
     * @throws LocalizedException
     */
    private function getArea(TestCase $test): string
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        $parser = Bootstrap::getObjectManager()->create(\Magento\TestFramework\Fixture\Parser\AppArea::class);
        $converter = static fn ($info) => $info['area'];
        $classAppIsolationState =  array_map($converter, $parser->parse($test, ParserInterface::SCOPE_CLASS))
            ?: ($annotations['class'][self::ANNOTATION_NAME] ?? []);
        $methodAppIsolationState =  array_map($converter, $parser->parse($test, ParserInterface::SCOPE_METHOD))
            ?: ($annotations['method'][self::ANNOTATION_NAME] ?? []);
        return current($methodAppIsolationState ?: $classAppIsolationState) ?: Application::DEFAULT_APP_AREA;
    }
}
