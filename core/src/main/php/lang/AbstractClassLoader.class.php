<?php
/* This class is part of the XP framework
 * 
 * $Id$
 */
 
  uses('lang.IClassLoader');
  
  /** 
   * Loads a class from the filesystem
   * 
   * @test  xp://net.xp_framework.unittest.reflection.ClassLoaderTest
   * @see   xp://lang.XPClass#forName
   */
  abstract class AbstractClassLoader extends Object implements IClassLoader {
    public $path= '';
    
    /**
     * Load the class by the specified name
     *
     * @param   string class fully qualified class name io.File
     * @return  lang.XPClass
     * @throws  lang.ClassNotFoundException in case the class can not be found
     */
    public function loadClass($class) {
      return new XPClass($this->loadClass0($class));
    }
    
    /**
     * Returns URI suitable for include() given a class name
     *
     * @param   string class
     * @return  string
     */
    protected abstract function classUri($class);

    /**
     * Load the class by the specified name
     *
     * @param   string class fully qualified class name io.File
     * @return  string class name
     * @throws  lang.ClassNotFoundException in case the class can not be found
     * @throws  lang.ClassFormatException in case the class format is invalud
     */
    public function loadClass0($class) {
      if (isset(xp::$cl[$class])) return xp::reflect($class);

      // Load class
      $package= NULL;
      xp::$cl[$class]= $this->getClassName().'://'.$this->path;
      xp::$cll++;
      try {
        $r= include($this->classUri($class));
      } catch (ClassLoadingException $e) {
        xp::$cll--;

        $decl= NULL;
        if (NULL === $package) {
          $decl= substr($class, (FALSE === ($p= strrpos($class, '.')) ? 0 : $p + 1));
        } else {
          $decl= strtr($class, '.', '�');
        }

        // If class was declared, but loading threw an exception it means
        // a "soft" dependency, one that is only required at runtime, was
        // not loaded, the class itself has been declared.
        if (class_exists($decl, FALSE) || interface_exists($decl, FALSE)) {
          raise('lang.ClassDependencyException', $class, array($this), $e);
        }

        // If otherwise, a "hard" dependency could not be loaded, eg. the
        // base class or a required interface and thus the class could not
        // be declared.
        raise('lang.ClassLinkageException', $class, array($this), $e);
      }
      xp::$cll--;
      if (FALSE === $r) {
        unset(xp::$cl[$class]);
        throw new ClassNotFoundException($class, array($this));
      }
      
      // Register it
      if (NULL === $package) {
        if (FALSE === ($p= strrpos($class, '.'))) {
          $name= $class;
        } else {
          $name= substr($class, $p+ 1);
          if (!class_exists($name, FALSE) && !interface_exists($name, FALSE)) {
            $name= strtr($class, '.', '\\');
            if (!class_exists($name, FALSE) && !interface_exists($name, FALSE)) {
              unset(xp::$cl[$class]);
              raise('lang.ClassFormatException', 'Class "'.$name.'" not declared in loaded file');
            }
          } else {
            class_alias($name, strtr($class, '.', '\\'));
          }
        }
      } else {
        $name= strtr($class, '.', '�');
        class_alias($name, strtr($class, '.', '\\'));
      }

      xp::$cn[$name]= $class;
      method_exists($name, '__static') && xp::$cli[]= array($name, '__static');
      if (0 === xp::$cll) {
        $invocations= xp::$cli;
        xp::$cli= array();
        foreach ($invocations as $inv) call_user_func($inv);
      }
      return $name;
    }

    /**
     * Checks whether two class loaders are equal
     *
     * @param   lang.Generic cmp
     * @return  bool
     */
    public function equals($cmp) {
      return $cmp instanceof self && $cmp->path === $this->path;
    }

    /**
     * Returns a hashcode for this class loader
     *
     * @return string
     */
    public function hashCode() {
      return 'cl@'.$this->path;
    }

    /**
     * Returns a unique identifier for this class loader instance
     *
     * @return  string
     */
    public function instanceId() {
      return $this->path;
    }

    /**
     * Creates a string representation
     *
     * @return  string
     */
    public function toString() {
      $segments= explode(DIRECTORY_SEPARATOR, $this->path);
      if (sizeof($segments) > 6) {
        $path= '...'.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, array_slice($segments, -6));
      } else {
        $path= $this->path;
      }
      return str_replace('ClassLoader', 'CL', $this->getClass()->getSimpleName()).'<'.$path.'>';
    }
  }
?>
