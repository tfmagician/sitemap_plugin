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
        'Sitemap.Sitemap',
    );

    /**
     * Uses helpers.
     *
     * @access public
     * @var array
     */
    var $helpers = array(
        'Sitemap.Sitemap',
    );

    /**
     * Views sitemap.
     *
     * @access public
     */
    function index()
    {
        $this->set('items', $this->Sitemap->createSitemap());
    }

}
