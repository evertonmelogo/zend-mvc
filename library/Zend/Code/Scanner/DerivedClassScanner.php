<?php

namespace Zend\Code\Scanner;

use Zend\Code\Scanner\DirectoryScanner,
    Zend\Code\Scanner\ClassScanner,
    Zend\Code\Exception;

class DerivedClassScanner extends ClassScanner
{

    /**
     * @var Zend\Code\Scanner\DirectoryScanner
     */
    protected $directoryScanner = null;
    
    /**
     * @var Zend\Code\Scanner\ClassScanner
     */  
    protected $classScanner = null;
    protected $parentClassScanners = array();
    
    public function __construct(ClassScanner $classScanner, DirectoryScanner $directoryScanner)
    {
        $this->classScanner = $classScanner;
        $this->directoryScanner = $directoryScanner;
        
        $currentScannerClass = $classScanner;
        
        while ($currentScannerClass && $currentScannerClass->hasParentClass()) {
            $currentParentClassName = $currentScannerClass->getParentClass(); 
            $this->parentClassScanners[$currentParentClassName] = null;
            if ($directoryScanner->hasClass($currentParentClassName)) {
                $currentParentClass = $directoryScanner->getClass($currentParentClassName);
                $this->parentClassScanners[$currentParentClassName] = $currentParentClass;
                $currentScannerClass = $currentParentClass;
            } else {
                $currentScannerClass = false;
            }
        }
    }
    
    public function getName()
    {
        return $this->classScanner->getName();
    }
    
    public function getShortName()
    {
        return $this->classScanner->getShortName();
    }
    
    public function isInstantiable()
    {
        return $this->classScanner->isInstantiable();
    }
    
    public function isFinal()
    {
        return $this->classScanner->isFinal();
    }

    public function isAbstract()
    {
        return $this->classScanner->isAbstract();
    }
    
    public function isInterface()
    {
        return $this->classScanner->isInterface();
    }

    public function getParentClasses()
    {
        return array_keys($this->parentClassScanners);
    }
    
    public function hasParentClass()
    {
        return ($this->classScanner->getParentClass() != null);
    }
    
    public function getParentClass()
    {
        return $this->classScanner->getParentClass();
    }
    
    public function getInterfaces()
    {
        $interfaces = $this->classScanner->getInterfaces();
        foreach ($this->parentClassScanners as $pClassScanner) {
            $interfaces = array_merge($interfaces, $pClassScanner->getInterfaces());
        }
        return $interfaces;
    }
    
    public function getConstants()
    {
        $constants = $this->classScanner->getConstants();
        foreach ($this->parentClassScanners as $pClassScanner) {
            $constants = array_merge($constants, $pClassScanner->getConstants());
        }
        return $constants;
    }
    
    public function getProperties($returnScannerProperty = false)
    {
        $properties = $this->classScanner->getProperties($returnScannerProperty);
        foreach ($this->parentClassScanners as $pClassScanner) {
            $properties = array_merge($properties, $pClassScanner->getProperties($returnScannerProperty));
        }
        return $properties;
    }

    public function getMethods($returnScannerMethod = false)
    {
        $methods = $this->classScanner->getMethods($returnScannerMethod);
        foreach ($this->parentClassScanners as $pClassScanner) {
            $methods = array_merge($methods, $pClassScanner->getMethods($returnScannerMethod));
        }
        return $methods;
    }
    
    public function getMethod($methodNameOrInfoIndex, $returnScannerClass = 'Zend\Code\Scanner\MethodScanner')
    {
        if ($this->classScanner->hasMethod($methodNameOrInfoIndex)) {
            return $this->classScanner->getMethod($methodNameOrInfoIndex, $returnScannerClass);
        }
        foreach ($this->parentClassScanners as $pClassScanner) {
            if ($pClassScanner->hasMethod($methodNameOrInfoIndex)) {
                return $pClassScanner->getMethod($methodNameOrInfoIndex, $returnScannerClass);
            }
        }
        throw new Exception\InvalidArgumentException(sprintf(
            'Method %s not found in %s',
            $methodNameOrInfoIndex,
            $this->classScanner->getName()
        ));
    }
    
    public function hasMethod($name)
    {
        if ($this->classScanner->hasMethod($name)) {
            return true;
        }
        foreach ($this->parentClassScanners as $pClassScanner) {
            if ($pClassScanner->hasMethod($name)) {
                return true;
            }
        }
        return false;
    }
    
    public static function export()
    {
        // @todo
    }
    
    public function __toString()
    {
        // @todo
    }

    
}