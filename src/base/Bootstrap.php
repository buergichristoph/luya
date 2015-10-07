<?php

namespace luya\base;

use Yii;
use yii\base\Object;

/**
 * Base class for luya bootsrapping proccess.
 *
 * @author nadar
 */
abstract class Bootstrap implements \yii\base\BootstrapInterface
{
    /**
     * @var string|array Readonly variable contains all module Objects.
     */
    private $_modules = null;

    /**
     * Boostrap method will be invoken by Yii Application bootrapping proccess containing
     * the Application ($app) Object to get/set data.
     * 
     * @param object $app Luya Application `luya\base\Application`.
     * @return void
     */
    public function bootstrap($app)
    {
        $this->extractModules($app);
        $this->beforeRun($app);
        $this->registerComponents($app);
        $this->run($app);
    }
    
    /**
     * Extract and load all modules from the Application-Object.
     *
     * @param object $app Luya Application `luya\base\Application`.
     * @return void
     */
    public function extractModules($app)
    {
        if ($this->_modules === null) {
            foreach ($app->getModules() as $id => $obj) {
                // create module object
                $moduleObject = Yii::$app->getModule($id);
                // see if the module is a luya base module, otherwise ignore
                if ($moduleObject instanceof \luya\base\Module) {
                    $this->_modules[$id] = $moduleObject;
                }
            }
        }
    }

    /**
     * Check if a Module exists in the module list `getModules()`.
     * 
     * @param string $module The name of the Module
     * @return boolean
     */
    public function hasModule($module)
    {
        return array_key_exists($module, $this->_modules);
    }

    /**
     * Return all modules prepared by `extractModules()` method.
     * 
     * @return array An array containg all modules where the key is the module name and 
     * the value is the Module Object `luya\base\Module`.
     */
    public function getModules()
    {
        return $this->_modules;
    }

    /**
     * Register all components from the modules `registerComponents()` method to the
     * Applcation.
     * 
     * @param object $app Luya Appliation `\luya\base\Application`.
     * @return void;
     */
    private function registerComponents($app)
    {
        foreach ($this->getModules() as $id => $module) {
            Yii::setAlias('@'.$id, $module->getBasePath());

            if (method_exists($module, 'registerComponents')) {
                foreach ($module->registerComponents() as $componentId => $definition) {
                    $app->set($componentId, $definition);
                }
            }
        }
    }

    /**
     * This method will be invoke before the `run()` method.
     * 
     * @param object $app Luya Application `luya\base\Application`
     * @return void
     */
    abstract public function beforeRun($app);

    /**
     * This method will be invoke after the `beforeRun()` method.
     * 
     * @param object $app Luya Application `luya\base\Application`
     * @return void
     */
    abstract public function run($app);
}
