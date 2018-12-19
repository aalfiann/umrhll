<?php include 'Core.php';
    header('Content-Type:text/plain');

    echo 'User-Agent: *
Allow: /

Sitemap: '.Core::getInstance()->basepath.'/sitemap.xml';
?>