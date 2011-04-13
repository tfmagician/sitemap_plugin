<?php
/**
 * SitemapHelper
 *
 * @package sitemap
 * @subpackage sitemap.views.helpers
 */
class SitemapHelper extends AppHelper
{

    /**
     * create sitemap as XML markup.
     *
     * @access public
     * @param array $items
     * @return string
     */
    function create($items)
    {
        $markup = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($items as $url => $item){
            $markup .= '<url>';
            $markup .= '<loc>' . $url . '</loc>';
            $markup .= '<lastmod>' . $item['lastmod'] . '</lastmod>';
            $markup .= '<changefreq>' . $item['changefreq'] . '</changefreq>';
            $markup .= '<priority>' . $item['priority'] . '</priority>';
            $markup .= '</url>';
        }
        $markup .= '</urlset>';
        return $markup;
    }

}
