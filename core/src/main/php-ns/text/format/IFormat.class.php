<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  /**
   * Format base class
   *
   * @purpose  Provide a base class to all format classes
   * @see      xp://text.format.MessageFormat#setFormatter
   */
  class IFormat extends \lang\Object {
    public
      $formatString = '';

    /**
     * Constructor
     *
     * @param   string f default NULL format string
     */
    public function __construct($f= NULL) {
      $this->formatString= $f;
    }

    /**
     * Get an instance
     *
     * @return  text.format.Format
     */
    public function getInstance($class) {
      static $instance= array();
      
      if (!isset($instance[$class])) $instance[$class]= new $class();
      return $instance[$class];
    }
      
    /**
     * Apply format to argument
     *
     * @param   var fmt
     * @param   var argument
     * @return  string
     * @throws  lang.IllegalAccessException
     */
    public function apply($fmt, $argument) { 
      throw new \lang\IllegalAccessException('Calling apply method of base class text.format.Format');
    }
    
    /**
     * Formats this message with the given arguments
     *
     * @param   var* args
     * @throws  lang.FormatException
     */
    public function format() {
      $a= func_get_args();
      return $this->apply($this->formatString, $a);
    }
  }
?>
