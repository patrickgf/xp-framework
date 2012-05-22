<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  namespace scriptlet\xml\portlet;
 use scriptlet\xml\portlet\Portlet;

  /**
   * PortletContainer
   *
   * @purpose  Container class
   */
  class PortletContainer extends \lang\Object {
    public
      $portlets= array();

    /**
     * Add Portlets
     *
     * @param   string classname
     * @param   string layout
     * @return  xml.portlet.Portlet
     */
    public function addPortlet($classname, $layout= NULL) {
      with ($portlet= \lang\XPClass::forName($classname)->newInstance()); {
        $portlet->setLayout($layout);
        $this->portlets[]= $portlet;
      }      
      return $portlet;
    }

    /**
     * Process container
     *
     * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @param   scriptlet.xml.XMLScriptletResponse response 
     * @param   scriptlet.xml.Context context
     */
    public function process($request, $response, $context) {
      $rundata= new RunData();
      $rundata->request= $request;
      $rundata->context= $context;

      $node= $response->addFormResult(new \xml\Node('portlets'));

      for ($i= 0, $s= sizeof($this->portlets); $i < $s; $i++) {
        $portlet= $node->addChild(new \xml\Node('portlet', NULL, array(
          'class'   => $this->portlets[$i]->getClassName(),
          'layout' =>  $this->portlets[$i]->getLayout()
        )));
        
        try {
          $content= $this->portlets[$i]->getContent($rundata);
        } catch (\lang\Throwable $e) {
          $response->addFormError($e->getClassName(), '*', $e->getMessage());
          return;
        }
        $content && $portlet->addChild($content);
      }
    }  
  }
?>
