<?php

include 'database/config-login.php';

$sql = "UPDATE orders 
        SET status = 'cancelled' 
        WHERE status IN ('pending','processing')
        AND NOW() > DATE_ADD(created_at, INTERVAL 1 DAY)";

mysqli_query($conn, $sql);
?>
