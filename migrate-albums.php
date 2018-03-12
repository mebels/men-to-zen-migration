<?php
header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set("display_errors", 1);

$dbconnect_old = new mysqli("localhost", "men_user", "men-password", "menalto_database");
$dbconnect_new = new mysqli("localhost", "zen_user", "zen-password", "zenphoto_database");

if (!$dbconnect_old || !$dbconnect_new) {
$mysqliconnecterror = mysqli_connect_error();
exit("Connect failed: %s\n $mysqliconnecterror");
}

$db_select_new_album = mysqli_query($dbconnect_new, "SELECT `id`,`folder`,`title`,`desc` FROM `albums`");

//$i = 0;
while($db_selectrows_new_album = mysqli_fetch_object($db_select_new_album)) {

//$i++;
//if ($i < 0) { continue; }

$db_selectrows_new_album_id = $db_selectrows_new_album->id;
$db_selectrows_new_album_folder = $db_selectrows_new_album->folder;
$db_selectrows_new_album_title = $db_selectrows_new_album->title;
$db_selectrows_new_album_desc = $db_selectrows_new_album->desc;

$db_select_old_album = mysqli_query($dbconnect_old, "SELECT `album_cover_item_id`,`relative_path_cache`,`title`,`description`,`level` FROM `items` WHERE `relative_path_cache`='" . mysqli_real_escape_string($dbconnect_old, $db_selectrows_new_album_folder) . "'");

while($db_selectrows_old_album = mysqli_fetch_object($db_select_old_album)) {

$db_selectrows_old_album_relative_path_cache = $db_selectrows_old_album->relative_path_cache;
$db_selectrows_old_album_title = $db_selectrows_old_album->title;
$db_selectrows_old_album_desc = $db_selectrows_old_album->description;
$db_selectrows_old_album_album_cover_item_id = $db_selectrows_old_album->album_cover_item_id;
$db_selectrows_old_album_level = $db_selectrows_old_album->level;


$db_select_old_album_album_cover_item_id = mysqli_query($dbconnect_old, "SELECT `id`,`relative_path_cache` FROM `items` WHERE `id`='" . $db_selectrows_old_album_album_cover_item_id . "'");
$db_selectrows_old_album_thumb = mysqli_fetch_object($db_select_old_album_album_cover_item_id);
$old_album_thumb_path = $db_selectrows_old_album_thumb->relative_path_cache;
$new_album_thumb_path = "/" . $old_album_thumb_path;


$db_select_old_album_level = mysqli_query($dbconnect_old, "SELECT `weight`,`relative_path_cache` FROM `items` WHERE `level`='" . $db_selectrows_old_album_level . "'");

$old_album_weights_array = array();
while($db_select_old_album_level_weight = mysqli_fetch_object($db_select_old_album_level)) {
$old_album_weight = $db_select_old_album_level_weight->weight;
$old_album_relative_path_cache = $db_select_old_album_level_weight->relative_path_cache;

$old_album_relative_path_cache = $old_album_relative_path_cache;

if (strpos($old_album_relative_path_cache, "/")) {
if (str_replace(strrchr($old_album_relative_path_cache, "/"), "", $old_album_relative_path_cache) == str_replace(strrchr($db_selectrows_new_album_folder, "/"), "", $db_selectrows_new_album_folder)) {
$old_album_weights_array[$old_album_relative_path_cache] = $old_album_weight;
}
} else {
$old_album_weights_array[$old_album_relative_path_cache] = $old_album_weight;
}
}

asort($old_album_weights_array, SORT_NUMERIC);
$new_album_weights_array = array();
$o = 0;
foreach ($old_album_weights_array as $key => $value) {
$new_album_weights_array[$key] = $o;
$o++;
}

$new_sort_order = $new_album_weights_array[$db_selectrows_new_album_folder];

echo "\n<br>";
echo "\n<br>zenphoto id: ";
echo $db_selectrows_new_album_id;
echo "\n<br>zenphoto: ";
echo $db_selectrows_new_album_folder;
echo "\n<br>menalto: ";
echo $db_selectrows_old_album_relative_path_cache . "'";
echo "\n<br>title: ";
echo $db_selectrows_old_album_title;
echo "\n<br>desc: ";
echo substr($db_selectrows_old_album_desc, 0, 100);
echo "\n<br>thumb : ";
echo $new_album_thumb_path;
echo "\n<br>sort_order : ";
echo $new_sort_order;
echo "\n<br>sort_type: manual";
echo "<br><br>\n\n";

$database_update_albums = "UPDATE albums SET `title`='" . mysqli_real_escape_string($dbconnect_new, $db_selectrows_old_album_title) . "', `desc`='" . mysqli_real_escape_string($dbconnect_new, $db_selectrows_old_album_desc) . "', `thumb`='" . $new_album_thumb_path . "', `sort_order`='" . $new_sort_order . "', `sort_type`='manual' WHERE `id`='" . $db_selectrows_new_album_id . "'";

if ($dbconnect_new->query($database_update_albums) === TRUE) {
    echo "Update successfull<br><br>\n\n";
} else {
    echo "Update error: " . $dbconnect_new->error .  "<br><br>\n\n";
}

}
//if ($i > 10) { break; }
}

mysqli_close($dbconnect_old);
mysqli_close($dbconnect_new);
?>
