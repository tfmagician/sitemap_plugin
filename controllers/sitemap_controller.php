<?php
/**
 * Sitemap Controller
 *
 * @package sitemap
 * @subpackage sitemap.controllers
 */
class SitemapController extends SitemapAppController
{

    /**
     * layout
     *
     * @access public
     * @var string
     */
    var $layout = 'default';

    /**
     * Does not use database.
     *
     * @access public
     * @var array()
     */
    var $uses = array();

    /**
     * Uses components.
     *
     * @access public
     * @var array
     */
    var $components = array(
        'RequestHandler',
    );

    /**
     * Instance for SitemapPlugin settings.
     *
     * @access public
     * @var Object
     */
    var $Config = null;

    /**
     * Items to create sitemap view.
     *
     * @access public
     * @var array
     */
    var $items = array();

    /**
     * Constructor
     *
     * @access private
     */
    function __construct()
    {
        parent::__construct();

        $path = CONFIGS.'sitemap.php';
        if (!file_exists($path)) {
            trigger_error('Could not find sitemap.php', E_USER_WARNING);
            return;
        }
        include($path);
        if (!class_exists('SITEMAP')) {
            trigger_error('Could not find SITEMAP class.', E_USER_WARNING);
            return;
        }
        $this->Config = new SITEMAP();
    }

    /**
     * Gets all actions of specific controller.
     *
     * @access private
     * @param string $controllerName
     * @return array
     */
    function _getControllerActions($controllerName)
    {
        $controllerName = Inflector::camelize($controllerName);
        App::import('Controller', $controllerName);
        $splitControllerName = split('.', $controllerName);
        if (count($splitControllerName) == 2) {
            $controllerName = $splitControllerName[1];
        }
        $controllerName = $controllerName.'Controller';
        $Controller = new $controllerName();
        return $Controller->methods;
    }

    /**
     * beforeFilter callback
     *
     * Initials SitemapController::$items.
     *
     * @access public
     */
    function beforeFilter()
    {
        if (!$this->RequestHandler->isXml()) {
            $this->cakeError('error404');
        }

        $default = $this->Config->default;
        $sitemaps = $this->Config->sitemaps;

        $items = array();
        foreach ($sitemaps as $sitemap) {
            $params = array();
            foreach (array('changefreq', 'priority', 'lastmod') as $param) {
                if (isset($sitemap[$param])) {
                    $params[$param] = $sitemap[$param];
                }
            }
            $params = $params + $default;

            if (is_string($sitemap['url'])) {
                $items[Router::url($sitemap['url'], true)] = $params;
                continue;
            }

            if (!isset($sitemap['model'])) {
                $actions = array($sitemap['url']['action']);
                if ($sitemap['url']['action'] == '*') {
                    $actions = $this->_getControllerActions($sitemap['url']['controller']);
                }
                foreach ($actions as $action) {
                    $sitemap['url']['plugin'] = null;
                    $sitemap['url']['action'] = $action;
                    $items[Router::url($sitemap['url'], true)] = $params;
                }
            } else {
                $Model = ClassRegistry::init($sitemap['model']);
                if (!isset($sitemap['field'])) {
                    $sitemap['field'] = $Model->primaryKey;
                }
                $key = array_search(':'.$sitemap['field'], $sitemap['url']);
                if ($key === false) {
                    $items[Router::url($sitemap['url'], true)] = $params;
                    continue;
                }

                $fields = array($sitemap['field']);
                $schema = $Model->schema();
                if (isset($schema['modified'])) {
                    $fields[] = 'modified';
                }
                $findParams = array(
                    'conditions' => array(),
                    'fields' => $fields,
                    'recursive' => -1,
                );
                $datas = $Model->find('all', $findParams);
                foreach ($datas as $data) {
                    $url = $sitemap['url'];
                    $url[$key] = $data[$Model->alias][$sitemap['field']];
                    $url = Router::url($url, true);
                    $items[$url] = $params;
                    if (isset($data[$Model->alias]['modified'])) {
                        $items[$url]['lastmod'] = date('Y-m-d', strtotime($data[$Model->alias]['modified']));
                    }
                }
            }
        }
        $this->items = $items;
    }

    /**
     * Views sitemap.
     *
     * @access public
     */
    function index()
    {
        $this->set('items', $this->items);
    }

}
