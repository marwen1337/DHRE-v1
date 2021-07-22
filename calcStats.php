<?php
/**
 * Created by PhpStorm.
 * User: marvi
 * Date: 01.10.2019
 * Time: 20:33
 */

require_once 'inc/AppCore.php';

$core = new AppCore(dirname(__FILE__) . "/config/default_config.php", dirname(__FILE__));

$stmt = $core->getPDO()->prepare("SELECT * FROM stats ORDER BY stats_date ASC");
$stmt->execute();

$views = array();
$views_total = 0;

foreach ($stmt->fetchAll() as $item){
    if(empty($views[$item['stats_sid']][$item['stats_date']])){
        $views[$item['stats_sid']][$item['stats_date']] = 1;
    }else{
        $views[$item['stats_sid']][$item['stats_date']] = $views[$item['stats_sid']][$item['stats_date']] + 1;
    }
    $views_total++;
}

$stmt = $core->getPDO()->prepare('TRUNCATE stats');
$stmt->execute();

$stmt = $core->getPDO()->prepare("SELECT short_id, short_stats FROM shortlinks");
$stmt->execute();
foreach ($stmt->fetchAll() as $item){
    $data = empty($item['short_stats']) ? array() : json_decode($item['short_stats'], true);
    if(!empty($views[$item['short_id']])){
        foreach ($views[$item['short_id']] as $key => $value){
            if(empty($data[$key]['views'])){
                $data[$key]['views'] = $value;
            }else{
                $data[$key]['views'] = $data[$key]['views'] + $value;
            }
        }
    }
    if(empty($data[date("Y-m-d")]['views'])){
        $data[date("Y-m-d")]['views'] = 0;
    }
    $stmt = $core->getPDO()->prepare("UPDATE shortlinks SET short_stats = :short_stats WHERE short_id = :short_id");
    $stmt->execute(array(":short_stats" => json_encode($data), ":short_id" => $item['short_id']));
}

echo "Calculated Views: " . $views_total . "<br>";
echo "Calculated Shortlinks: " . count($views);