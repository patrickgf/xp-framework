<?php
/* This class is part of the XP framework
 *
 * $Id: AbstractProcessorTest.class.php 8971 2006-12-27 15:27:10Z friebe $
 */

  namespace net::xp_framework::unittest::xml;
 
  ::uses('unittest.TestCase');

  /**
   * Test XSL processor
   *
   * @see      xp://xml.IXSLProcessor
   * @purpose  Unit Test
   */
  class AbstractProcessorTest extends unittest::TestCase {
    public
      $processor      = NULL,
      $xmlDeclaration = '';
      
    /**
     * Compares XML after stripping all whitespace between tags of both 
     * expected and actual strings.
     *
     * @see     xp://unittest.TestCase#assertEquals
     * @param   string expect
     * @param   string actual
     * @throws  unittest.AssertionFailedError
     */
    public function assertXmlEquals($expect, $actual) {
      return $this->assertEquals(
        $this->xmlDeclaration.preg_replace('#>[\s\r\n]+<#', '><', trim($expect)),
        preg_replace('#>[\s\r\n]+<#', '><', trim($actual))
      );
    }

    /**
     * Returns the PHP extension needed for this processor test to work
     *
     * @return  string
     */
    public function neededExtension() { }

    /**
     * Returns the XSL processor instance to be used
     *
     * @return  &xml.IXSLProcessor
     */
    public function processorInstance() { }

    /**
     * Returns the XSL processor's default output charset
     *
     * @return  string
     */
    public function processorCharset() { }

    /**
     * Tests 
     *
     * @throws  unittest.PrerequisitesNotMetError
     */
    public function setUp() {
      if (!extension_loaded($ext= $this->neededExtension())) {
        throw(new PrerequisitesNotMetError($ext.' extension not loaded'));
      }
      $this->processor= $this->processorInstance();
      $this->xmlDeclaration= '<?xml version="1.0" encoding="'.$this->processorCharset().'"?>';
    }

    /**
     * Tests the setParam() and getParam() methods
     *
     */
    #[@test]
    public function paramAccessors() {
      $this->processor->setParam('a', 'b');
      $this->assertEquals('b', $this->processor->getParam('a'));
    }

    /**
     * Tests the setBase() and getBase() methods
     *
     */
    #[@test]
    public function baseAccessors() {
      $path= rtrim(realpath('../xml/'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
      $this->processor->setBase($path);
      $this->assertEquals($path, $this->processor->getBase());
    }

    /**
     * Tests the setBase() adds trailing DIRECTORY_SEPARATOR
     *
     */
    #[@test]
    public function setBaseAddsTrailingDirectorySeparator() {
      $path= rtrim(realpath('../xml/'), DIRECTORY_SEPARATOR);
      $this->processor->setBase($path);
      $this->assertEquals($path.DIRECTORY_SEPARATOR, $this->processor->getBase());
    }

    /**
     * Tests the setParams() methods
     *
     */
    #[@test]
    public function setParams() {
      $this->processor->setParams(array(
        'a'     => 'b',
        'left'  => 'one',
        'right' => 'two'
      ));
      $this->assertEquals('b', $this->processor->getParam('a')) &&
      $this->assertEquals('one', $this->processor->getParam('left')) &&
      $this->assertEquals('two', $this->processor->getParam('right'));
    }

    /**
     * Tests a transformation that will result in an empty result
     *
     */
    #[@test]
    public function transformationWithEmptyResult() {
      $this->processor->setXMLBuf('<document/>');
      $this->processor->setXSLBuf('
        <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
          <xsl:output method="text"/>
        </xsl:stylesheet>
      ');
      $this->processor->run();
      $this->assertEquals('', $this->processor->output());
    }

    /**
     * Tests a transformation
     *
     */
    #[@test]
    public function transformationWithResult() {
      $this->processor->setXMLBuf('<document/>');
      $this->processor->setXSLBuf('
        <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
          <xsl:output method="xml" encoding="utf-8"/>
          <xsl:template match="/">
            <b>Hello</b>
          </xsl:template>
        </xsl:stylesheet>
      ');
      $this->processor->run();
      $this->assertXmlEquals('<b>Hello</b>', $this->processor->output());
    }

    /**
     * Tests a transformation with parameters
     *
     */
    #[@test]
    public function transformationWithParameter() {
      $this->processor->setXMLBuf('<document/>');
      $this->processor->setXSLBuf('
        <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
          <xsl:param name="input"/>
          <xsl:output method="xml" encoding="utf-8"/>
          <xsl:template match="/">
            <b><xsl:value-of select="$input"/></b>
          </xsl:template>
        </xsl:stylesheet>
      ');
      $this->processor->setParam('input', 'Parameter #1');
      $this->processor->run();
      $this->assertXmlEquals('<b>Parameter #1</b>', $this->processor->output());
    }

    /**
     * Tests a transformation with parameters
     *
     */
    #[@test]
    public function transformationWithParameters() {
      $this->processor->setXMLBuf('<document/>');
      $this->processor->setXSLBuf('
        <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
          <xsl:param name="left"/>
          <xsl:param name="right"/>
          <xsl:output method="xml" encoding="utf-8"/>
          <xsl:template match="/">
            <b><xsl:value-of select="$left + $right"/></b>
          </xsl:template>
        </xsl:stylesheet>
      ');
      $this->processor->setParams(array(
        'left'  => '1',
        'right' => '2',
      ));
      $this->processor->run();
      $this->assertXmlEquals('<b>3</b>', $this->processor->output());
    }

    /**
     * Tests a transformation with malformed XML
     *
     */
    #[@test, @expect('xml.TransformerException')]
    public function malformedXML() {
      $this->processor->setXMLBuf('@@MALFORMED@@');
      $this->processor->setXSLBuf('<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>');
      $this->processor->run();
    }

    /**
     * Tests a transformation with malformed XSL
     *
     */
    #[@test, @expect('xml.TransformerException')]
    public function malformedXSL() {
      $this->processor->setXMLBuf('<document/>');
      $this->processor->setXSLBuf('@@MALFORMED@@');
      $this->processor->run();
    }
  }
?>