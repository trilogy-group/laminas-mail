<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Mail\Header;

use Laminas\Mail\Header\GenericHeader;
use PHPUnit_Framework_TestCase as TestCase;

class GenericHeaderTest extends TestCase
{
    /**
     * @group ZF2015-04
     */
    public function testSplitHeaderLineRaisesExceptionOnInvalidHeader()
    {
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException');
        GenericHeader::splitHeaderLine(
            'Content-Type' . chr(32) . ': text/html; charset = "iso-8859-1"' . "\nThis is a test"
        );
    }

    public function fieldNames()
    {
        return array(
            'append-chr-13'  => array("Subject" . chr(13)),
            'append-chr-127' => array("Subject" . chr(127)),
        );
    }

    /**
     * @dataProvider fieldNames
     * @group ZF2015-04
     */
    public function testRaisesExceptionOnInvalidFieldName($fieldName)
    {
        $header = new GenericHeader();
        $this->setExpectedException('Laminas\Mail\Header\Exception\InvalidArgumentException', 'name');
        $header->setFieldName($fieldName);
    }

    public function fieldValues()
    {
        return array(
            'empty-lines'             => array("\n\n\r\n\r\n\n"),
            'trailing-newlines'       => array("Value\n\n\r\n\r\n\n"),
            'leading-newlines'        => array("\n\n\r\n\r\n\nValue"),
            'surrounding-newlines'    => array("\n\n\r\n\r\n\nValue\n\n\r\n\r\n\n"),
            'split-value'             => array("Some\n\n\r\n\r\n\nValue"),
            'leading-split-value'     => array("\n\n\r\n\r\n\nSome\n\n\r\n\r\n\nValue"),
            'trailing-split-value'    => array("Some\n\n\r\n\r\n\nValue\n\n\r\n\r\n\n"),
            'surrounding-split-value' => array("\n\n\r\n\r\n\nSome\n\n\r\n\r\n\nValue\n\n\r\n\r\n\n"),
        );
    }

    /**
     * @dataProvider fieldValues
     * @group ZF2015-04
     * @param string $fieldValue
     */
    public function testCRLFsequencesAreEncodedOnToString($fieldValue)
    {
        $header = new GenericHeader('Foo');
        $header->setFieldValue($fieldValue);

        $serialized = $header->toString();
        $this->assertNotContains("\n", $serialized);
        $this->assertNotContains("\r", $serialized);
    }

    /**
     * @group ZF2015-04
     */
    public function testCastingToStringHandlesContinuationsProperly()
    {
        $encoded = '=?UTF-8?Q?foo=0D=0A=20bar?=';
        $raw = "foo\r\n bar";

        $header = new GenericHeader('Foo');
        $header->setFieldValue($raw);

        $this->assertEquals($raw, $header->getFieldValue());
        $this->assertEquals($encoded, $header->getFieldValue(GenericHeader::FORMAT_ENCODED));
        $this->assertEquals('Foo: ' . $encoded, $header->toString());
    }
}
