 <?php
header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set("display_errors", 1);

$dbconnect_old = new mysqli("localhost", "menalto-user", "menalto-password", "menalto_database");
$dbconnect_new = new mysqli("localhost", "zenphoto-user", "zenphoto-password", "zenphoto-database");

if (!$dbconnect_old || !$dbconnect_new) {
$mysqliconnecterror = mysqli_connect_error();
exit("Connect failed: %s\n $mysqliconnecterror");
}


$db_select_new_tags = mysqli_query($dbconnect_new, "SELECT `name` FROM `tags`");
$zentags_array = array();
while($db_selectrows_new_tags = mysqli_fetch_object($db_select_new_tags)) {
$db_selectrows_new_tags_name = $db_selectrows_new_tags->name;
array_push($zentags_array, $db_selectrows_new_tags_name);
}

$db_select_old_tags = mysqli_query($dbconnect_old, "SELECT `name` FROM `tags`");
while($db_selectrows_old_tags = mysqli_fetch_object($db_select_old_tags)) {
$db_selectrows_old_tags_name = $db_selectrows_old_tags->name;
if (!in_array($db_selectrows_old_tags_name, $zentags_array)) {
$tags_insert_into = "INSERT INTO `tags` (name) VALUES ('".$db_selectrows_old_tags_name."')";

if (mysqli_query($dbconnect_new, $tags_insert_into)) {
    echo "tag ".'"'.$db_selectrows_old_tags_name.'"'." created successfully <br>\n";
} else {
    echo "Error: " . $tags_insert_into . "<br>\n" . mysqli_error($dbconnect_new) . "\n";
}
} else {
echo '"'.$db_selectrows_old_tags_name.'"'." tag is already in Zenphoto database <br>\n";
}
}

$db_select_new_tags = mysqli_query($dbconnect_new, "SELECT `id`,`name` FROM `tags`");
$zen_tags_array = array();
while($db_selectrows_new_tags = mysqli_fetch_object($db_select_new_tags)) {
$db_selectrows_new_tags_id = $db_selectrows_new_tags->id;
$db_selectrows_new_tags_name = $db_selectrows_new_tags->name;
$zen_tags_array[$db_selectrows_new_tags_id] = $db_selectrows_new_tags_name;
}


$db_select_new_images = mysqli_query($dbconnect_new, "SELECT images.albumid, images.id AS imageid, images.filename, albums.id, albums.folder FROM images INNER JOIN albums ON images.albumid=albums.id");


$i = 0;
while($db_selectrows_new_images = mysqli_fetch_object($db_select_new_images)) {

$i++;
echo $i . "\n";
//if ($i < 0) { continue; }

$db_selectrows_new_images_imageid = $db_selectrows_new_images->imageid;
$db_selectrows_new_images_albumid = $db_selectrows_new_images->albumid;
$db_selectrows_new_images_filename = $db_selectrows_new_images->filename;
$db_selectrows_new_albums_id = $db_selectrows_new_images->id;
$db_selectrows_new_albums_folder = $db_selectrows_new_images->folder;

$db_selectrows_new_images_folder_filename = $db_selectrows_new_albums_folder . "/" . $db_selectrows_new_images_filename;


$db_select_old_image = mysqli_query($dbconnect_old, "SELECT `id`,`relative_path_cache`,`name`,`title`,`description`,`parent_id` FROM `items` WHERE items.relative_path_cache='" . mysqli_real_escape_string($dbconnect_old, $db_selectrows_new_images_folder_filename) . "'");

while($db_selectrows_old_image = mysqli_fetch_object($db_select_old_image)) {

$db_selectrows_old_image_id = $db_selectrows_old_image->id;
$db_selectrows_old_image_relative_path_cache = $db_selectrows_old_image->relative_path_cache;
$db_selectrows_old_image_name = $db_selectrows_old_image->name;
$db_selectrows_old_image_title = $db_selectrows_old_image->title;
$db_selectrows_old_image_desc = $db_selectrows_old_image->description;
$db_selectrows_old_image_parent_id = $db_selectrows_old_image->parent_id;


$db_select_old_image_tags = mysqli_query($dbconnect_old, "SELECT items.id AS itemsid, items_tags.item_id, items_tags.tag_id, tags.id AS tagsid, tags.name FROM ((items INNER JOIN items_tags ON items.id = items_tags.item_id) INNER JOIN tags ON items_tags.tag_id = tags.id) WHERE items.id=$db_selectrows_old_image_id");

while($db_selectrows_old_image_tags = mysqli_fetch_object($db_select_old_image_tags)) {
	
if (isset($db_selectrows_old_image_tags->name)) {

$tags_itemsid = $db_selectrows_old_image_tags->itemsid;
$tags_item_id = $db_selectrows_old_image_tags->item_id;
$tags_tag_id = $db_selectrows_old_image_tags->tag_id;
$tags_tagsid = $db_selectrows_old_image_tags->tagsid;
$tags_name = $db_selectrows_old_image_tags->name;
$tags_name_id = array_search($tags_name, $zen_tags_array);

echo "\n<br>";
echo "\n<br>zen img id='";
echo $db_selectrows_new_images_imageid . "'";
echo "\n<br>zenphoto: ";
echo $db_selectrows_new_images_folder_filename;
echo "\n<br>menalto: ";
echo $db_selectrows_old_image_relative_path_cache;
echo "\n<br>title: ";
echo $db_selectrows_old_image_title;
echo "\n<br>desc: ";
echo substr($db_selectrows_old_image_desc, 0, 100);
echo "\n<br>name: ";
echo $tags_name;
echo "\n<br>tagid: ";
echo $tags_name_id;
echo "\n<br>type: ";
echo "images";
echo "\n<br>objectid: ";
echo $db_selectrows_new_images_imageid;
echo "<br>\n";

$db_select_zen_obj_to_tag = mysqli_query($dbconnect_new, "SELECT `tagid`,`type`,`objectid` FROM `obj_to_tag`");
if (mysqli_num_rows($db_select_zen_obj_to_tag) === 0) {
$obj_to_tag_insert_into = "INSERT INTO `obj_to_tag` (tagid, type, objectid) VALUES ('".$tags_name_id."', 'images', '".$db_selectrows_new_images_imageid."')";

if (mysqli_query($dbconnect_new, $obj_to_tag_insert_into)) {
    echo "Image successfully tagged with ".'"'.$tags_name.'"'." <br><br>\n\n";
} else {
    echo "Error: " . $obj_to_tag_insert_into . "<br>\n" . mysqli_error($dbconnect_new) . "\n\n";
}
} else {

$db_selectrows_zen_obj_to_tag_tagid["tagid"] = array();
$db_selectrows_zen_obj_to_tag_type["type"] = array();
$db_selectrows_zen_obj_to_tag_objectid["objectid"] = array();
$x = 0;
while($db_selectrows_zen_obj_to_tag = mysqli_fetch_object($db_select_zen_obj_to_tag)) {
$db_selectrows_zen_obj_to_tag_tagid["tagid"][$x] = $db_selectrows_zen_obj_to_tag->tagid;
$db_selectrows_zen_obj_to_tag_type["type"][$x] = $db_selectrows_zen_obj_to_tag->type;
$db_selectrows_zen_obj_to_tag_objectid["objectid"][$x] = $db_selectrows_zen_obj_to_tag->objectid;
$x++;
}
if (in_array($db_selectrows_new_images_imageid, $db_selectrows_zen_obj_to_tag_objectid["objectid"])) {

$objectid_keys = array_keys($db_selectrows_zen_obj_to_tag_objectid["objectid"], $db_selectrows_new_images_imageid);

foreach ($objectid_keys as $value) {
if (($db_selectrows_zen_obj_to_tag_tagid["tagid"][$value] == $tags_name_id) && ($db_selectrows_zen_obj_to_tag_type["type"][$value] == "images") && ($db_selectrows_zen_obj_to_tag_objectid["objectid"][$value] == $db_selectrows_new_images_imageid)) {
echo "Image is tagged already in Zenphoto database <br><br>\n\n";
} else {
$obj_to_tag_insert_into = "INSERT INTO `obj_to_tag` (tagid, type, objectid) VALUES ('".$tags_name_id."', 'images', '".$db_selectrows_new_images_imageid."')";

if (mysqli_query($dbconnect_new, $obj_to_tag_insert_into)) {
    echo "Image successfully tagged with ".'"'.$tags_name.'"'." <br><br>\n\n";
} else {
    echo "Error: " . $obj_to_tag_insert_into . "<br>\n" . mysqli_error($dbconnect_new) . "\n\n";
}
}
}
} else {
$obj_to_tag_insert_into = "INSERT INTO `obj_to_tag` (tagid, type, objectid) VALUES ('".$tags_name_id."', 'images', '".$db_selectrows_new_images_imageid."')";

if (mysqli_query($dbconnect_new, $obj_to_tag_insert_into)) {
    echo "Image successfully tagged with ".'"'.$tags_name.'"'." <br><br>\n\n";
} else {
    echo "Error: " . $obj_to_tag_insert_into . "<br>\n" . mysqli_error($dbconnect_new) . "\n\n";
}
}
}

}

}

}

//if ($i > 10) { break; }

}

mysqli_close($dbconnect_old);
mysqli_close($dbconnect_new);
?>
