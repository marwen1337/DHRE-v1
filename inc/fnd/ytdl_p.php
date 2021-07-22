<?php


declare(strict_types=1);

require __DIR__ . '../../ytdl/ytdownloader/vendor/autoload.php';

use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;

$yt = new YoutubeDl();
$collection = $yt->download(
    Options::create()
        ->extractAudio(true)
        ->audioFormat('mp3')
        ->audioQuality(0) // best
        ->output('%(title)s.%(ext)s')
        ->url('https://www.youtube.com/watch?v=oDAw7vW7H0c')
);

foreach ($collection->getVideos() as $video) {
    if ($video->getError() !== null) {
        echo "Error downloading video: {$video->getError()}.";
    } else {
        $video->getFile(); // audio file
    }
}