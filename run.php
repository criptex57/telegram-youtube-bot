<?php
require('youtube-dl.class.php');
try {
    /** @var yt_downloader $object */
    $object = new yt_downloader("https://www.youtube.com/watch?v=VT5ol0cEiRY", TRUE, "audio");

    print_r($object->audio);
}
catch (Exception $e) {
    die($e->getMessage());
}