<?php
/* logs expiration (in days) */
define('LOGS_EXPIRE', 7);
define('E404_EXPIRE', 60);
define('SHOW_RESULT', FALSE);

require_once 'mozilla.cz/wp-config.php';

function log_end($string) {
    if(SHOW_RESULT) {
        printf("%s\n", $string);
    }
    file_put_contents('data/delete_redirection_plugin_logs_and_404.txt', date("Y-m-d H:i:s").' '.$string."\n", FILE_APPEND | LOCK_EX);
    die();
}

/* connect to db */
$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

/* check connection */
if (mysqli_connect_errno()) {
    $status = sprintf("Connection failed: %s", mysqli_connect_error());
    log_end($status);
}

/* queries */
$queries[] = 'DELETE FROM `'.$table_prefix.'redirection_logs` WHERE `created` < ADDDATE(NOW(), INTERVAL - '.LOGS_EXPIRE.' DAY)';
$queries[] = 'DELETE FROM `'.$table_prefix.'redirection_404` WHERE `created` < ADDDATE(NOW(), INTERVAL - '.E404_EXPIRE.' DAY)';
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