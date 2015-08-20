<?php

namespace Object69\Modules\Route69;

class Route{

    protected $routes = array();
    protected $always = array();
    protected $strict = true;

    /**
     * Always set the following settings for each call
     * @param array $settings
     * @return Route
     */
    public function always(array $settings = null){
        $this->always = $settings;
        return $this;
    }

    /**
     * Sets a new route to be tested
     * @param string $path The path of the route
     * @param array $settings The settings fo the route
     * @return Route
     */
    public function when($path, array $settings = null){
        $this->routes[] = array(
            "path"     => $path,
            "settings" => $settings
        );
        return $this;
    }

    /**
     * If no when statement gets executed default to this
     * @param array $settings
     * @return Route
     */
    public function otherwise(array $settings){
        $this->routes[] = array(
            "fallback" => true,
            "settings" => $settings
        );
        return $this;
    }

    /**
     * Gets a list of all the setup routes
     * @return type
     */
    public function getRoutes(){
        return $this->routes;
    }

    /**
     * Gets a list of the always settings
     * @return type
     */
    public function getAlways(){
        return $this->always;
    }

    /**
     * Turns on/off strict mode
     * @param type $isStrict
     */
    public function setStrict($isStrict){
        $this->strict = (bool)$isStrict;
    }

    /**
     * Gets the current strictness
     * @return type
     */
    public function getStrict(){
        return $this->strict;
    }

}
