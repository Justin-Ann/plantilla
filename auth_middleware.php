<?php
function check_auth() {
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }
}

function check_admin() {
    check_auth();
    if($_SESSION["role"] !== "admin") {
        header("location: dashboard.php");
        exit;
    }
}

function check_email_verified() {
    check_auth();
    if(!isset($_SESSION["email_verified"]) || $_SESSION["email_verified"] !== true) {
        header("location: verification-required.php");
        exit;
    }
}
