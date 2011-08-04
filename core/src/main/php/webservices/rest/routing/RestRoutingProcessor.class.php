<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'webservices.rest.RestDataCaster',
    'webservices.rest.routing.RestRoutingItem'
  );
  
  /**
   * REST route processor
   *
   * @test    xp://net.xp_framework.unittest.rest.routing.RestRoutingProcessorTest
   * @purpose Router
   */
  class RestRoutingProcessor extends Object {
    protected
      $bindings= array();
    
    /**
     * Bind resource
     * 
     * @param string name The name of binding
     * @param lang.Object resource The resource to bind
     */
    public function bind($name, $resource) {
      $this->bindings[$name]= $resource;
    }
    
    /**
     * Helper function for parsing binding name
     * 
     * @param string name The name
     * @return string[]
     */
    protected function parseName($name) {
      if (!preg_match('/^([^\[]*)(\[(.*)\])?$/', $name, $matches)) {
        throw new IllegalArgumentException('Can not parse binding: '.$name);
      }
      
      return array(
        $matches[1],
        isset($matches[3]) ? $matches[3] : NULL
      );
    }
    
    /**
     * Retrieve binding
     * 
     * @param string token The token name of binding
     * @return mixed
     */
    public function getBinding($token) {
      list($name, $key)= $this->parseName($token);
      
      if (!isset($this->bindings[$name])) {
        throw new IllegalArgumentException('No binding to inject '.$token);
      }
      
      if ($key !== NULL) {
        $binding= (array)$this->bindings[$name];
        
        if (!isset($binding[$key])) {
          throw new IllegalArgumentException('Binding '.$name.' has no key '.$key);
        }
        
        return $binding[$key];
        
      } else {
        return $this->bindings[$name];
      }
    }
    
    /**
     * Process
     * 
     * @param webservices.rest.routing.RestRoutingItem routing The routing item to process
     * @param mixed[] values The argument values
     * @return mixed
     */
    public function execute(RestRoutingItem $routing, $values) {
      $args= array();
      
      // Build list of injected arguments
      foreach ($routing->getArgs()->getInjections() as $name) {
        $args[]= $this->getBinding($name);
      }

      // Add named parameters
      foreach ($routing->getArgs()->getArguments() as $i => $name) {
        if ($i < sizeof($routing->getArgs()->getInjections())) continue;  // Skip injection arguments

        $args[]= RestDataCaster::complex(
          RestDataCaster::simple($values[$name]),
          $routing->getArgs()->getArgumentType($name)
        );
      }
      
      return $routing->getTarget()->process($args);
    }
  }
?>
