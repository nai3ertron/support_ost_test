<?php
require_once("login.mongo.php");
    $servername = "ztidev.com";
    $username = "em";
    $password = "ZTIDEVzeal1tech";

    // Create connection
    $conn = new mysqli($servername, $username, $password);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $sql = "SELECT * FROM em.ost_user_account
            inner join em.ost_user_email on 
            em.ost_user_account.user_id = em.ost_user_email.user_id
            join em.ost_user on em.ost_user.id = em.ost_user_account.user_id 
            ORDER BY em.ost_user_account.user_id asc";
      
    $result = $conn->query($sql);

?>