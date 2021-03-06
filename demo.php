<?php
//We load our packages automatically using Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/class.php';

session_start();

if (!empty($_POST)) {
    $hasExtraParam = $extraParam = array();
    $parsing_has_occurred = false;
    if (isset($_POST["feed_url"])) {
        $RSSFeedList = array_filter(explode(chr(13), $_POST["feed_url"]));


        //Initializing our reader and writer
        $feedReader = new FeedReader();
        $feedWriter = new FeedWriter(PARSED_FEED_FULLPATH);

        foreach ($RSSFeedList as $key => $url) {
            //Getting the feed url with possible extra parameter
            $dataParams = explode(',', $url);

            $filteredUrl = trim(strip_tags($dataParams[0]));
            if (filter_var($filteredUrl, FILTER_VALIDATE_URL) === false) {
                continue;
            }

            //if a feed has an extra parameter
            if (isset($dataParams[1])) {
                $hasExtraParam[] = true;
                $extraParam[] = $dataParams[1];
            } else {
                $hasExtraParam[] = false;
                $extraParam[] = null;
            }

            $parsing_has_occurred = true;

            $items = $feedReader->getItems($filteredUrl);

            /**
             * Going through each RSS feed item
             *
             * @var $item SimplePie_Item
             */
            foreach ($items as $item) {
                //We get the fields from our feed we need to write to disk
                $itemData = FeedReader::readFeedItem($item);

                //We write the fields to disk
                if (!empty($itemData)) {
                    //We take the extra parameter tacked on to the end of the feed url and add it to the parsed result.
                    if ($hasExtraParam[$key] === true) {
                        $itemData[] = $extraParam[$key];
                    }
                    $feedWriter->writeFeed(FeedReader::stringifyFeedItemFields($itemData));
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
<div>
    <h3>RSS Feed file parser</h3>

    <div id="mainForm">
        <form enctype="multipart/form-data"
              action="<?php
              echo $_SERVER["PHP_SELF"];
              ?>" method="POST">
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