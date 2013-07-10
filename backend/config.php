<?php

$config = array();
$config['appId'] = '192351964261671';
$config['secret'] = '2c0ce846356ab46e072b68aae2bcc3db';
$config['scope'] = 'email,publish_stream,manage_pages,user_photos,photo_upload';
$config['fileUpload'] = 'true';
$errors['DATABASE_CON'] = '-1';
$errors['VALUE'] = '-2';
$errors['EMAIL'] = '-3';
$errors['NOT_IN_DATABASE'] = '-4';
$errors['NOT_A_PAGE'] = '-5';
$errors['NOT_A_USER'] = '-6';
$errors['IMAGE_TO_LARGE'] = '-7';
$errors['WRONG_IMAGE_TYPE'] = '-8';
$notifications['comment'] = '1';
$notifications['post_deletet'] = '2';
$notifications['post_added'] = '3';
$notifications["post_updated"] = '4';
$status[0] = "noch nicht angesehen";
$status[1] = "korrektur";
$status[2] = "freigegeben";
?>
