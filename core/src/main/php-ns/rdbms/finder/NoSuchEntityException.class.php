<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  namespace rdbms\finder;
 use rdbms\finder\FinderException;

  /**
   * Indicates a specific entity could not be found
   *
   * @see      xp://rdbms.finder.FinderException#find
   * @purpose  Chained exception
   */
  class NoSuchEntityException extends FinderException {
  
  }
?>
