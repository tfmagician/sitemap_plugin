<?php
/**
 * SitemapComponent
 *
 * @package sitemap
 * @subpackage sitemap.controllers.components
 */
class SitemapComponent extends Object
{

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
     * initialize callback
     *
     * @access public
     * @param Controller $Controller
     * @return void
     */
    function initialize(&$Controller)
    {
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
     * startup callback.
     *
     * @access public
     * @param Controller $Controller
     * @return void
     */
    function startup(&$Controller)
    {
        $gz = $this->RequestHandler->prefers('gz');
        if (!$this->RequestHandler->isXml() && !$gz) {
            $this->cakeError('error404');
        }
        if ($gz) {
            $filename = basename($Controller->params['url']['url']);
            $Controller->header('content-type: application/x-gzip');
            $Controller->header('Content-Disposition: attachment; filename="' . $filename . '"');
            $this->RequestHandler->renderAs($Controller, 'gz');
        }
    }

    /**
     * create sitemap as array.
     *
     * @access public
     * @return array
     */
    function createSitemap()
    {
        if ($items = Cache::read('sitemap')) {
            return $items;
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

            $sitemap['url']['plugin'] = null;
            if (isset($sitemap['model'])) {
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
            } elseif (isset($sitemap['paginate'])) {
                $key = array_search(':page', $sitemap['url']);
                if ($key === false) {
                    $items[Router::url($sitemap['url'], true)] = $params;
                    continue;
                }

                $paging = $this->_getPagingParams($sitemap['url'], $sitemap['paginate']);
                $paging = current($paging);
                for ($page = 1; $page <= $paging['pageCount']; $page ++) {
                    $url = $sitemap['url'];
                    $url[$key] = $page;
                    $url = Router::url($url, true);
                    $items[$url] = $params;
                }
            } else {
                $actions = array($sitemap['url']['action']);
                if ($sitemap['url']['action'] == '*') {
                    $actions = $this->_getControllerActions($sitemap['url']['controller']);
                }
                foreach ($actions as $action) {
                    $sitemap['url']['action'] = $action;
                    $items[Router::url($sitemap['url'], true)] = $params;
                }
            }
        }
        Cache::write('sitemap', $items);
        return $items;
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
        return $this->_getController($controllerName)->methods;
    }

    /**
     * Gets paging params of specific controller.
     *
     * @access private
     * @param array $url
     * @param mixed $args
     * @return array
     */
    function _getPagingParams($url, $args)
    {
        if ($args !== true) {
            $controller =& $this->_getController($url['controller']);
            $action = 'paginate';
        } else {
            foreach ($url as $key => $one) {
                if ($one == ':page') {
                    $url[$key] = 1;
                }
            }
            $parsed = Router::parse(Router::url($url));
            $controller =& $this->_getController($parsed['controller']);
            $action = $parsed['action'];
            $args = $parsed['pass'];
        }
        $controller->params['url']['page'] = $controller->params['page'] = 1;
        $controller->constructClasses();
        $controller->dispatchMethod($action, $args);
        return $controller->params['paging'];
    }

    /**
     * Gets specific controller instance.
     *
     * @access private
     * @param string $controllerName
     * @return array
     */
    function _getController($controllerName)
    {
        static $controllers = array();

        $controllerName = Inflector::camelize($controllerName);
        App::import('Controller', $controllerName);
        $splitControllerName = split('.', $controllerName);
        if (count($splitControllerName) == 2) {
            $controllerName = $splitControllerName[1];
        }
        $controllerName = $controllerName.'Controller';

        if (!isset($controllers[$controllerName])) {
            $controllers[$controllerName] = new $controllerName();
        }
        return $controllers[$controllerName];

    }

}
