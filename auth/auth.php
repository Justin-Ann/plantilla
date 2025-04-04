<?php
session_start();

function requireLogin() {
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if(!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
        header("location: dashboard.php");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION["role"]) && $_SESSION["role"] === "admin";
}
?>
