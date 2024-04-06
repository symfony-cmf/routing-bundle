<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Tests\Unit\DependencyInjection;

use Symfony\Cmf\Component\Testing\Unit\XmlSchemaTestCase;

class XmlSchemaTest extends XmlSchemaTestCase
{
    private string $fixturesPath;
    private string $schemaPath;

    protected function setUp(): void
    {
        $this->fixturesPath = __DIR__.'/../../Fixtures/fixtures/config/';
        $this->schemaPath = __DIR__.'/../../../src/Resources/config/schema/routing-1.0.xsd';
    }

    public function testSchema(): void
    {
        $fixturesPath = $this->fixturesPath;
        $xmlFiles = array_map(static function ($file) use ($fixturesPath) {
            return $fixturesPath.$file;
        }, [
            'config.xml',
            'config1.xml',
            'config2.xml',
            'config3.xml',
            'config4.xml',
        ]);

        $this->assertSchemaAcceptsXml($xmlFiles, $this->schemaPath);
    }

    public function testSchemaInvalidatesTwoPersistenceLayers(): void
    {
        $this->assertSchemaRefusesXml($this->fixturesPath.'config_invalid1.xml', $this->schemaPath);
    }
}
