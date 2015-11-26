<?php
//We load our packages automatically using Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/class.php';

session_start();

if (!empty($_POST)) {
    $parsing_has_occurred = false;
    if (isset($_POST["feed_url"])) {
        $RSSFeedList = array_filter(explode(chr(13), $_POST["feed_url"]));


        //Initializing our reader and writer
        $feedReader = new FeedReader();
        $feedWriter = new FeedWriter(PARSED_FEED_FULLPATH);

        foreach ($RSSFeedList as $url) {
            $filteredUrl = trim(strip_tags($url));
            if (filter_var($filteredUrl, FILTER_VALIDATE_URL) === false) {
                continue;
            }

            $parsing_has_occurred = true;

            $feedReader->init($filteredUrl);
            $data = $feedReader->getData();
            $items = $data->get_items();

            /**
             * Going through each RSS feed item
             *
             * @var $item SimplePie_Item
             */
            foreach ($items as $item) {
                //We get the fields from our feed we need to write to disk
                $itemData = array(
                    $item->get_title(),
                    $item->get_link(),
                    $item->get_date('Y-m-d H:i:s'),
                );

                //We write the fields to disk
                if (!empty($itemData)) {
                    $feedWriter->writeFeed(implode(PARSED_RSS_FIELD_DELIMITER,
                        array_map('html_entity_decode', $itemData)));
                }
            }
        }

        if ($parsing_has_occurred === true) {
            header("Content-Type: application/force download");
            header("Content-Transfer-Encoding: binary");
            header("Pragma: no-cache");
            header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
            header("Expires: 0");
            header("Content-Disposition: attachment; filename=feeds.txt");
            echo file_get_contents(PARSED_FEED_FULLPATH);
        } else {
            display_page();
        }
    }
} else {
    display_page();
    ?>

    <?php
}
session_destroy();


function display_page()
{
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf8"/>
        <link rel="stylesheet" type="text/css" href="/assets/base.css"/>
    </head>
<body>
<div id="header">
    <h3>RSS Feed file parser</h3>

    <div id="mainForm">
        <form enctype="multipart/form-data"
              action="<?php
              echo $_SERVER["PHP_SELF"];
              ?>" method="POST"
              style="padding: 10px 10px 20px 10px">
            <div class="row">
                    <textarea class="form-control" rows="30" cols="50" id="feed_url" name="feed_url"
                              placeholder="<Enter feed urls here, one url per line>"></textarea>
            </div>
            <div class="row">
                <input class="button-primary" type="submit" value="Parse feeds"/>
            </div>
        </form>
    </div>
</div>
    <?php
}