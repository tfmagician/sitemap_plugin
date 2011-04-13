<?php
App::import('Controller', 'Sitemap.Sitemap');
App::import('Component', 'Sitemap.Sitemap');

Mock::generate('SitemapComponent', 'TestSitemapController_Sitemap');
Mock::generatePartial(
    'SitemapController', 'TestSitemapController',
    array('redirect', 'render', 'cakeError')
);

class SitemapControllerTestCase extends CakeTestCase
{

    function startTest()
    {
        $this->SitemapController = new TestSitemapController();
        $this->SitemapController->Sitemap = new TestSitemapController_Sitemap();
    }

    function endTest()
    {
        unset($this->SitemapController);
    }

    function testIndex()
    {
        $Sitemap = $this->SitemapController->Sitemap;
        $Sitemap->expectOnce('createSitemap', array());
        $Sitemap->setReturnValue('createSitemap', 'createSitemap');

        $this->SitemapController->index();
        $expected = array(
            'items' => 'createSitemap',
        );
        $this->assertEqual($expected, $this->SitemapController->viewVars);
    }

}
