<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($items as $url => $item): ?>
    <url>
        <loc><?php echo $url; ?></loc>
        <lastmod><?php echo $item['lastmod']; ?></lastmod>
        <changefreq><?php echo $item['changefreq']; ?></changefreq>
        <priority><?php echo $item['priority']; ?></priority>
    </url>
    <?php endforeach; ?>
</urlset>
