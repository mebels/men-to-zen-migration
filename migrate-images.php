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

$db_select_new_images = mysqli_query($dbconnect_new, "SELECT images.albumid, images.id AS imageid, images.filename, albums.id, albums.folder FROM images INNER JOIN albums ON images.albumid=albums.id");

while($db_selectrows_new_images = mysqli_fetch_object($db_select_new_images)) {

$db_selectrows_new_images_imageid = $db_selectrows_new_images->imageid;
$db_selectrows_new_images_albumid = $db_selectrows_new_images->albumid;
$db_selectrows_new_images_filename = $db_selectrows_new_images->filename;
$db_selectrows_new_albums_id = $db_selectrows_new_images->id;
$db_selectrows_new_albums_folder = $db_selectrows_new_images->folder;

$db_selectrows_new_images_folder_filename = $db_selectrows_new_albums_folder . "/" . $db_selectrows_new_images_filename;

$db_select_old_image = mysqli_query($dbconnect_old, "SELECT `relative_path_cache`,`name`,`title`,`description`,`parent_id` FROM `items` WHERE `type`='photo'");

while($db_selectrows_old_image = mysqli_fetch_object($db_select_old_image)) {

$db_selectrows_old_image_relative_path_cache = $db_selectrows_old_image->relative_path_cache;
$db_selectrows_old_image_name = $db_selectrows_old_image->name;
$db_selectrows_old_image_title = $db_selectrows_old_image->title;
$db_selectrows_old_image_desc = $db_selectrows_old_image->description;
$db_selectrows_old_image_parent_id = $db_selectrows_old_image->parent_id;

if ($db_selectrows_new_images_folder_filename == $db_selectrows_old_image_relative_path_cache) {

$db_select_old_image_parent_id = mysqli_query($dbconnect_old, "SELECT `weight`,`relative_path_cache` FROM `items` WHERE `parent_id`=$db_selectrows_old_image_parent_id");

$old_image_weights_array = array();
while($db_select_old_image_parent_id_weight = mysqli_fetch_object($db_select_old_image_parent_id)) {
$old_image_weight = $db_select_old_image_parent_id_weight->weight;
$old_image_relative_path_cache = $db_select_old_image_parent_id_weight->relative_path_cache;

if (strpos($db_selectrows_old_image_relative_path_cache, "/")){
$old_image_relative_path_cache_short = str_replace(strrchr($db_selectrows_old_image_relative_path_cache, "/"), "", $db_selectrows_old_image_relative_path_cache);
if (strpos($old_image_relative_path_cache, $old_image_relative_path_cache_short) === 0) {
$old_image_weights_array[$old_image_weight] = $old_image_relative_path_cache;
}
} else {
$old_image_weights_array[$old_image_weight] = $old_image_relative_path_cache;
}
}

ksort($old_image_weights_array, SORT_NUMERIC);
$new_image_weights_array = array_values($old_image_weights_array);
$new_sort_order = array_search($db_selectrows_new_images_folder_filename, $new_image_weights_array);

echo "\n<br>";
echo "\n<br>zen img id='";
echo $db_selectrows_new_images_imageid . "'";
echo "\n<br>zenphoto: ";
echo $db_selectrows_new_images_folder_filename;
echo "\n<br>";
echo "relative_path_cache='" . $db_selectrows_old_image_relative_path_cache . "'";
echo "\n<br>title: ";
echo $db_selectrows_old_image_title;
echo "\n<br>desc: ";
echo substr($db_selectrows_old_image_desc, 0, 100);
echo "\n<br>albumid: ";
echo $db_selectrows_new_images_albumid;
echo "\n<br>sort_order: ";
echo $new_sort_order;
echo "<br><br>\n\n";

$database_update_images = "UPDATE images SET `title`='" . mysqli_real_escape_string($dbconnect_new, $db_selectrows_old_image_title) . "', `desc`='" . mysqli_real_escape_string($dbconnect_new, $db_selectrows_old_image_desc) . "', `sort_order`='" . $new_sort_order . "' WHERE `id`='" . $db_selectrows_new_images_imageid . "'";

if ($dbconnect_new->query($database_update_images) === TRUE) {
    echo "Update successfull";
} else {
    echo "Update error: " . $dbconnect_new->error;
}

echo "<br><br>\n\n";

}

}

}

mysqli_close($dbconnect_old);
mysqli_close($dbconnect_new);
?>
