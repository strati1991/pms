<?php

$config = array();
$config['appId'] = '192351964261671';
$config['secret'] = '2c0ce846356ab46e072b68aae2bcc3db';
$config['scope'] = 'email,publish_stream,manage_pages,user_photos,photo_upload';
$config['fileUpload'] = 'true';
$errors = array();
$errors['DATABASE_CON'] = '-1';
$errors['VALUE'] = '-2';
$errors['EMAIL'] = '-3';
$errors['NOT_IN_DATABASE'] = '-4';
$errors['NOT_A_PAGE'] = '-5';
$errors['NOT_A_USER'] = '-6';
$errors['IMAGE_TO_LARGE'] = '-7';
$errors['WRONG_IMAGE_TYPE'] = '-8';
$errors['NOT_YET_UPLOADED'] = '-9';
$notifications = array();
$notifications['comment'] = '1';
$notifications['post_deletet'] = '2';
$notifications['post_added'] = '3';
$notifications["post_updated"] = '4';
$status = array();
$status[0] = "noch nicht angesehen";
$status[1] = "korrektur";
$status[2] = "freigegeben";
$role_description = array();
$role_description[0] = "d&uuml;rfen Posts erstellen</br>und ihre eigenen Posts bearbeiten";
$role_description[1] = "d&uuml;rfen alle Posts</br>sehen,bearbeiten und freigeben ";
$role_description[2] = "d&uuml;rfen zus&auml;tzlich User</br>hinzuf&uuml;gen und l&ouml;schen";
?>
