<?php
App::import('Controller', 'Sitemap.Sitemap');

if (!defined('FULL_BASE_URL')) {
    define('FULL_BASE_URL', 'http://test.com');
}

Cache::clear();
App::build(
    array(
        'controllers' => array(
            APP.'plugins'.DS.'sitemap'.DS.'tests'.DS.'app'.DS.'controllers'.DS,
        ),
    ),
    true
);

Mock::generatePartial(
    'SitemapController', 'TestSitemapController',
    array('redirect', 'render')
);

class TEST_SITEMAP
{
    var $default = array(
        'changefreq' => 'monthly',
        'priority' => '1.0',
    );

    var $sitemaps = array(
        array(
            'url' => array('controller' => 'posts', 'action' => 'first', 'arg'),
        ),
        array(
            'url' => array('controller' => 'contents', 'action' => '*'),
        ),
        array(
            'url' => '/this/is/url',
        ),
        array(
            'url' => array('controller' => 'posts', 'action' => 'detail', ':id'),
            'model' => 'Post',
            'field' => 'id',
        ),
        array(
            'url' => array('controller' => 'posts', 'action' => 'second'),
            'changefreq' => 'weekly',
            'priority' => '0.8',
        ),
    );
}

class SitemapControllerTestCase extends CakeTestCase
{

    var $fixtures = array(
        'plugin.sitemap.post',
    );

    function startTest()
    {
        $this->SitemapController = new TestSitemapController();
        $this->SitemapController->Config = new TEST_SITEMAP();
    }

    function endTest()
    {
        unset($this->SitemapController);
    }

    function testBeforeFilter()
    {
        $this->SitemapController->beforeFilter();
        $expected = array(
            FULL_BASE_URL.'/posts/first/arg' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
            ),
            FULL_BASE_URL.'/contents/first' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
            ),
            FULL_BASE_URL.'/contents/second' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
            ),
            FULL_BASE_URL.'/this/is/url' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
            ),
            FULL_BASE_URL.'/posts/detail/1' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
            ),
            FULL_BASE_URL.'/posts/detail/2' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
            ),
            FULL_BASE_URL.'/posts/detail/3' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
            ),
            FULL_BASE_URL.'/posts/second' => array(
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ),
        );
        $this->assertEqual($expected, $this->SitemapController->items);
    }

    function testIndex()
    {
        $this->SitemapController->items = 'items';
        $this->SitemapController->index();
        $expected = array(
            'items' => 'items',
        );
        $this->assertEqual($expected, $this->SitemapController->viewVars);
    }

}
