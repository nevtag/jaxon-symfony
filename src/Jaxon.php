<?php

namespace Jaxon\AjaxBundle;

class Jaxon extends \Jaxon\Response\Response
{
    use \Jaxon\Framework\JaxonTrait;

    /**
     * The application URL
     * 
     * @var string
     */
    protected $baseUrl;

    /**
     * The application web dir
     * 
     * @var string
     */
    protected $baseDir;

    /**
     * The application root dir
     * 
     * @var string
     */
    protected $rootDir;

    /**
     * The application debug option
     * 
     * @var bool
     */
    protected $debug;

    /**
     * The template engine
     * 
     * @var \Symfony\Component\Templating\EngineInterface
     */
    protected $template;

    /**
     * Create a new Jaxon instance.
     *
     * @return void
     */
    public function __construct($kernel, $request, $template, $debug)
    {
        $this->jaxon = jaxon();
        $this->response = new Response();
        $this->view = new View($template);
        // Application URL and paths
        $this->baseUrl = $request->getBaseUrl();
        $this->baseDir = $request->getBasePath();
        $this->rootDir = realpath($kernel->getRootDir() . '/..');
        // Application debug option
        $this->debug = $debug;
    }

    /**
     * Initialize the Jaxon module.
     *
     * @return void
     */
    public function setup()
    {
        // This function should be called only once
        if(($this->setupCalled))
        {
            return;
        }
        $this->setupCalled = true;

        // Use the Composer autoloader
        $this->jaxon->useComposerAutoloader();
        // Jaxon library default options
        $this->jaxon->setOptions(array(
            'js.app.extern' => !$this->debug,
            'js.app.minify' => !$this->debug,
            'js.app.uri' => $this->baseUrl . '/jaxon/js',
            'js.app.dir' => $this->baseDir . '/jaxon/js',
        ));

        // Read and set the config options from the config file
        $config = $this->jaxon->readConfigFile($this->rootDir . '/app/config/jaxon.yml', 'jaxon_ajax.lib');

        // Jaxon application settings
        $appConfig = array();
        if(array_key_exists('jaxon_ajax', $config) &&
            array_key_exists('app', $config['jaxon_ajax']) &&
            is_array($config['jaxon_ajax']['app']))
        {
            $appConfig = $config['jaxon_ajax']['app'];
        }
        $controllerDir = (array_key_exists('dir', $appConfig) ? $appConfig['dir'] : $this->rootDir . '/src/Jaxon/App');
        $namespace = (array_key_exists('namespace', $appConfig) ? $appConfig['namespace'] : '\\Jaxon\\App');
        $excluded = (array_key_exists('excluded', $appConfig) ? $appConfig['excluded'] : array());
        // The public methods of the Controller base class must not be exported to javascript
        $controllerClass = new \ReflectionClass('\\Jaxon\\AjaxBundle\\Controller');
        foreach ($controllerClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $xMethod)
        {
            $excluded[] = $xMethod->getShortName();
        }

        // Set the request URI
        if(!$this->jaxon->getOption('core.request.uri'))
        {
            $this->jaxon->setOption('core.request.uri', 'jaxon');
        }
        // Register the default Jaxon class directory
        $this->jaxon->addClassDir($controllerDir, $namespace, $excluded);
    }
}
