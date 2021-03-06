<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'xp.codegen.AbstractOutput',
    'io.File',
    'lang.archive.Archive'
  );

  /**
   * Output for generation
   *
   * @purpose  Abstract base class
   */
  class ArchiveOutput extends AbstractOutput {
    protected
      $archive = NULL;
      
    /**
     * Constructor
     *
     * @param   string file
     */
    public function __construct($file) {
      $this->archive= new Archive(new File($file));
      $this->archive->open(ARCHIVE_CREATE);
    }

    /**
     * Store data
     *
     * @param   string name
     * @param   string data
     */
    protected function store($name, $data) {
      if (!$overwrite && $archive->contains($name)) {
        return;
      }
      $this->archive->addFileBytes(
        $name,
        dirname($name),
        basename($name),
        $data
      );
    }
    
    /**
     * Data for the given name already exists
     *
     * @param string $name
     * @return boolean
     */
    protected function exists($name) {
      return $archive->contains($name);
    }
    
    /**
     * Commit output
     *
     */
    public function commit() {
      $this->archive->create();
    }
  }
?>
