<?php

//We load our packages automatically using Composer's autoloader
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/class.php';


echo "<h3>RSS Feed file parser</h3>";


//Reading through the URL list
$fileReader = new FileReader(RSS_FEED_FILE);
$RSSFeedList = $fileReader->readFile();

if (empty($RSSFeedList)) {
    echo "<h4>No feeds to parse.</h4>";
}

//Initializing our reader and writer
$feedReader = new FeedReader();
$feedWriter = new FeedWriter(PARSED_FEED_FULLPATH);


//We go through the list of feeds to parse
foreach ($RSSFeedList as $url) {
    $feedReader->init($url);
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

    echo "Parsed url '" . $url . "'" . " (parsed items: " . $data->get_item_quantity() . ")<br/><br/>";
}
