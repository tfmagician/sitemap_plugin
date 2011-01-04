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
        $default = $this->Config->default;
        $sitemaps = $this->Config->sitemaps;

        $items = array();
        foreach ($sitemaps as $sitemap) {
            $params = array();
            foreach (array('changefreq', 'priority') as $param) {
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
                $findParams = array(
                    'conditions' => array(),
                    'fields' => array($sitemap['field']),
                    'recursive' => -1,
                );
                $data = $Model->find('list', $findParams);
                foreach ($data as $arg) {
                    $url = $sitemap['url'];
                    $url[$key] = $arg;
                    $items[Router::url($url, true)] = $params;
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
