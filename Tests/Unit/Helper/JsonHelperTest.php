<?php
namespace TYPO3\Eel\Tests\Unit;

/*
 * This file is part of the TYPO3.Eel package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\Eel\Helper\JsonHelper;

/**
 * Tests for JsonHelper
 */
class JsonHelperTest extends \TYPO3\Flow\Tests\UnitTestCase
{
    public function stringifyExamples()
    {
        return array(
            'string value' => array(
                'Foo', '"Foo"'
            ),
            'null value' => array(
                null, 'null'
            ),
            'numeric value' => array(
                42, '42'
            ),
            'array value' => array(
                array('Foo', 'Bar'), '["Foo","Bar"]'
            )
        );
    }

    /**
     * @test
     * @dataProvider stringifyExamples
     */
    public function stringifyWorks($value, $expected)
    {
        $helper = new JsonHelper();
        $result = $helper->stringify($value);
        $this->assertEquals($expected, $result);
    }

    public function parseExamples()
    {
        return array(
            'string value' => array(
                array('"Foo"'), 'Foo'
            ),
            'null value' => array(
                array('null'), null
            ),
            'numeric value' => array(
                array('42'), 42
            ),
            'array value' => array(
                array('["Foo","Bar"]'), array('Foo', 'Bar')
            ),
            'object value is parsed as associative array by default' => array(
                array('{"name":"Foo"}'), array('name' => 'Foo')
            ),
            'object value without associative array' => array(
                array('{"name":"Foo"}', false), (object)array('name' => 'Foo')
            )
        );
    }

    /**
     * @test
     * @dataProvider parseExamples
     */
    public function parseWorks($arguments, $expected)
    {
        $helper = new JsonHelper();
        $result = call_user_func_array(array($helper, 'parse'), $arguments);
        $this->assertEquals($expected, $result);
    }
}
