<?php
$conn = oci_connect('System', '1234', 'localhost/xe');
if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}
else{
    $msg = "database connected successfully";
}
?>