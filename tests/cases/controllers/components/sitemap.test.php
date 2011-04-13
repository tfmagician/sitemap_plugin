<?php
App::import('Component', 'Sitemap.Sitemap');
App::import('Component', 'RequestHandler');

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

Mock::generate('RequestHandlerComponent', 'TestSitemapComponent_RequestHandler');
Mock::generatePartial(
    'SitemapComponent', 'TestSitemapComponent',
    array('redirect', 'render', 'cakeError', 'prefers')
);

class TEST_SITEMAP
{
    var $default = array(
        'changefreq' => 'monthly',
        'priority' => '1.0',
        'lastmod' => '2011-01-30',
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
            'lastmod' => '2011-01-31',
        ),
        array(
            'url' => array('controller' => 'posts', 'action' => 'search', ':page'),
            'paginate' => array('Post'),
        ),
    );
}


class SitemapComponentTestCase extends CakeTestCase
{

    var $fixtures = array(
        'plugin.sitemap.post',
    );

    function startTest()
    {
        $this->SitemapComponent = new TestSitemapComponent();
        $this->SitemapComponent->Config = new TEST_SITEMAP();
        $this->SitemapComponent->RequestHandler = new TestSitemapComponent_RequestHandler();
    }

    function endTest()
    {
        unset($this->SitemapComponent);
    }

    function testCreateSitemap()
    {
        $ret = $this->SitemapComponent->createSitemap();
        $expected = array(
            FULL_BASE_URL.'/posts/first/arg' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
                'lastmod' => '2011-01-30',
            ),
            FULL_BASE_URL.'/contents/first' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
                'lastmod' => '2011-01-30',
            ),
            FULL_BASE_URL.'/contents/second' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
                'lastmod' => '2011-01-30',
            ),
            FULL_BASE_URL.'/this/is/url' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
                'lastmod' => '2011-01-30',
            ),
            FULL_BASE_URL.'/posts/detail/1' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
                'lastmod' => '2011-01-01',
            ),
            FULL_BASE_URL.'/posts/detail/2' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
                'lastmod' => '2011-01-02',
            ),
            FULL_BASE_URL.'/posts/detail/3' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
                'lastmod' => '2011-01-03',
            ),
            FULL_BASE_URL.'/posts/second' => array(
                'changefreq' => 'weekly',
                'priority' => '0.8',
                'lastmod' => '2011-01-31',
            ),
            FULL_BASE_URL.'/posts/search/1' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
                'lastmod' => '2011-01-30',
            ),
            FULL_BASE_URL.'/posts/search/2' => array(
                'changefreq' => 'monthly',
                'priority' => '1.0',
                'lastmod' => '2011-01-30',
            ),
        );
        $this->assertEqual($expected, $ret);
    }

}
