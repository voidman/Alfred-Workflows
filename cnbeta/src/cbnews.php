<?php
/**
 * a workflow for cnBeta news.
 *
 * @version 1.0b
 * @author David Lin(voidman.me@gmail.com)
 */

define('CNBETA_NEWS_ID', 'cbnews');

define('CNBETA_NEWS_NOT_FOUND_TITLE', 'No news found.');
define('CNBETA_NEWS_NOT_FOUND_CONTENT', 'Please try to visit http://www.cnbeta.com :)');

define('CNBETA_NEWS_URL_BASE', 'http://www.cnbeta.com');
define('CNBETA_NEWS_SHOW_NEWS_ICON', true);
define('CNBETA_NEWS_NEWS_ICON_DIR', 'newsicons');
define('CNBETA_NEWS_NEWS_ICON_DEFAULT', 'icon.png');

require 'simple_html_dom.php';
require 'workflows.php';

$w = new Workflows();

$context = stream_context_create(array(
                'http' => array(
                    'method' => "GET",
                    'timeout' => 10
                )
            )
        );
$html = file_get_html(CNBETA_NEWS_URL_BASE, 0, $context);

if (!$html) {
    $w->result(CNBETA_NEWS_ID, CNBETA_NEWS_URL_BASE, CNBETA_NEWS_NOT_FOUND, CNBETA_NEWS_NOT_FOUND_DESC, CNBETA_NEWS_NEWS_ICON_DEFAULT);
} else {
    foreach ($html->find('div.newslist') as $element) {

        $title = $element->find('strong', 0)->plaintext;
        $title = iconv('GB2312', 'UTF-8', $title);

        $url = $element->find('a', 0)->href;

        $id = 0;
        if (preg_match("/\/articles\/([0-9]+).htm/", $url, $matches)) {
            $id = $matches[1];
        }

        $content = $element->find('dd.desc > span', 0)->plaintext;
        $content = iconv('GB2312', 'UTF-8', trim($content));

        $content = trim(preg_replace("/感谢.+的投递/", '', $content));

        if (!CNBETA_NEWS_SHOW_NEWS_ICON) {
            $iconPath = CNBETA_NEWS_NEWS_ICON_DEFAULT;
        } else {
            $iconUrl = $element->find('dd.desc > a > img', 0)->src;
            $iconName = basename(($iconUrl));
            $iconPath = CNBETA_NEWS_NEWS_ICON_DIR . DIRECTORY_SEPARATOR . $iconName;
            if (!file_exists(CNBETA_NEWS_NEWS_ICON_DIR)) {
                mkdir(CNBETA_NEWS_NEWS_ICON_DIR);
            }
            if (!file_exists($iconPath)) {
                file_put_contents($iconPath, file_get_contents($iconUrl));
            }
            $iconPath = file_exists($iconPath) ? $iconPath : CNBETA_NEWS_NEWS_ICON_DEFAULT;
        }

        $w->result($id, CNBETA_NEWS_URL_BASE.$url, $title, $content, $iconPath);
    }

    if ( count( $w->results() ) == 0 ) {
        $w->result(CNBETA_NEWS_ID, CNBETA_NEWS_URL_BASE, CNBETA_NEWS_NOT_FOUND, CNBETA_NEWS_NOT_FOUND_DESC, CNBETA_NEWS_NEWS_ICON_DEFAULT);
    }

    $html->clear();
    unset($html);
}

echo $w->toxml();

unset($w);