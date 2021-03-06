<?php

namespace Jaxon\AjaxBundle;

class View
{
    protected $data;
    protected $template;

    public function __construct($template)
    {
        $this->data = array();
        $this->template = $template;
    }

    /**
     * Make a piece of data available for all views
     *
     * @param string        $name            The data name
     * @param string        $value           The data value
     * 
     * @return void
     */
    public function share($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Render a template
     *
     * @param string        $template        The template path
     * @param string        $data            The template data
     * 
     * @return mixed        The rendered template
     */
    public function render($template, array $data = array())
    {
        return trim($this->template->render($template, array_merge($this->data, $data)), "\n");
    }
}
