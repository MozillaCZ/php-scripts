<?php
define('SHOW_RESULT', FALSE);
define('LOCK_AFTER_DAYS', 31);

require_once 'forum.mozilla.cz/config.php';

function log_end($string) {
    if(SHOW_RESULT) {
        printf("%s\n", $string);
    }
    file_put_contents('data/lock_after_days.txt', date("Y-m-d H:i:s").' '.$string."\n", FILE_APPEND | LOCK_EX);
    die();
}

/* connect to db */
$link = mysqli_connect($dbhost, $dbuser, $dbpasswd, $dbname);

/* check connection */
if (mysqli_connect_errno()) {
    $status = sprintf("Connection failed: %s", mysqli_connect_error());
    log_end($status);
}

/* queries */
$queries[] = 'UPDATE '.$table_prefix.'topics AS t SET t.topic_status = 1 WHERE FROM_UNIXTIME(t.topic_last_post_time) + INTERVAL '.LOCK_AFTER_DAYS.' DAY < NOW() AND t.topic_status = 0';
try {
	foreach($queries as $query) {
		$result = mysqli_query($link, $query);
		if(SHOW_RESULT) {
			printf("&quot;%s&quot; result:\n", htmlentities($query));
			printf("<pre>\n");
			if (is_a($result, 'mysqli_result')) {
				while($row = mysqli_fetch_array($result)) {
					var_dump($row);
					echo "\n";
				}
			} else {
				var_dump($result);
			}
			printf("</pre>\n");
		}
		if (is_a($result, 'mysqli_result')) {
			mysqli_free_result($result);
		}
	}
} catch (Exception $e) {
    mysqli_close($link);
	$status = sprintf("Error: %s", $e->getMessage());
    log_end($status);
}
mysqli_close($link);
log_end('OK');
