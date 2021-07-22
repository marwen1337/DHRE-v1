<?php

require dirname(__FILE__) . "/ytdownloader/vendor/autoload.php";

use YoutubeDl\YoutubeDl;
use YoutubeDl\Exception\CopyrightException;
use YoutubeDl\Exception\NotFoundException;
use YoutubeDl\Exception\PrivateVideoException;

include dirname(__FILE__) . "/../inc/AppCore.php";

print_r("\n\n\n");
print_r("Creating Core instance...\n");
$core = new AppCore(dirname(__FILE__) . "/../config/default_config.php", dirname(__FILE__) . "/../");
print_r("Done!\n");

print_r("Establishing database connection...\n");
$stmt = $core->getPDO()->prepare("SELECT ytdl_token FROM ytdl WHERE ytdl_converted = 0");
$stmt->execute();
print_r("Done!\n");

print_r("Starting downloader script...\n");
$dl = new YoutubeDl([
    'extract-audio' => true,
    'audio-format' => 'mp3',
    'audio-quality' => 0, // best
    'output' => '%(id)s.%(ext)s',
]);

$dl->debug(function ($type, $buffer) {
    if (\Symfony\Component\Process\Process::ERR === $type) {
        echo 'ERR > ' . $buffer;
    } else {
        echo 'OUT > ' . $buffer;
    }
});

$dl->setDownloadPath(__DIR__ . "/downloads");
$dl->setBinPath('/usr/local/bin/youtube-dl');

print_r("Start video converting...\n");

foreach ($stmt->fetchAll() as $item){
    echo "Converting " . $item['ytdl_token'] . "\n";
    try {
        $video = $dl->download('https://youtube.com/watch?v=' . $item['ytdl_token']);
        $stmt = $core->getPDO()->prepare("UPDATE ytdl SET ytdl_title = :title, ytdl_converted = 1 WHERE ytdl_token = :token");
        $stmt->execute(array(":title" => $video->getTitle(), ":token" => $item['ytdl_token']));
    } catch (NotFoundException $e) {
        echo "Error, video not found: " . $item['ytdl_token'];
    } catch (PrivateVideoException $e) {
        echo "Error, video is private: " . $item['ytdl_token'];
    } catch (CopyrightException $e) {
        echo "Error, copyright strike: " . $item['ytdl_token'];
    } catch (\Exception $e) {
        echo "Error while downloading: " . $item['ytdl_token'];
    }
}

print_r("Done...\n");