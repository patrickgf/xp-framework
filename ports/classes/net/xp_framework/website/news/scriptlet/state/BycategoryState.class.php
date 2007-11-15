<?php
/* This class is part of the XP framework
 *
 * $Id: NewsState.class.php 8971 2006-12-27 15:27:10Z friebe $ 
 */

  uses('net.xp_framework.website.news.scriptlet.AbstractNewsListingState');

  /**
   * Handles /xml/news
   *
   * @purpose  State
   */
  class BycategoryState extends AbstractNewsListingState {

    /**
     * Retrieve parent category's ID
     *
     * @return  int
     */
    public function getParentCategory($request) {
      return ($request->getEnvValue('CATID')
        ? $request->getEnvValue('CATID')
        : 8
      );        
    }
    
    /**
     * Retrieve entries
     *
     * @param   rdbms.DBConnection db
     * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
     * @return  rdbms.ResultSet
     */
    public function getEntries($db, $request) {
      return $db->query('
        select distinct 
          entry.id as id,
          entry.title as title,
          entry.body as body,
          entry.author as author,
          entry.timestamp as timestamp,
          length(entry.extended) as extended_length,
          (select count(*) from serendipity_comments c where c.entry_id = entry.id) as num_comments
        from
          serendipity_entries entry,
          serendipity_entrycat matrix,
          serendipity_category category
        where
          (category.parentid = %1$d or category.categoryid = %1$d)
          and entry.isdraft = "false"
          and entry.id = matrix.entryid
          and matrix.categoryid = category.categoryid
        order by
          timestamp desc
        limit %2$d, 10
        ',
        $this->getParentCategory($request),
        $this->getOffset($request)
      );
    }
    
  }
?>
