<?php 
    include('./connection.php');
    session_start();
    if (isset($_SESSION['token'])) {
        if (verifyToken($pdo, $_SESSION['token'], $_SESSION['user'])) {
            $sql = "DELETE from tokens where user='$_SESSION[user]'";
            $query = $pdo->prepare($sql);
            $query->execute();
        }
        session_destroy();
    }
    header("Location: index.php");

    function verifyToken($pdo, $token, $user) {
        $sql = "SELECT id from tokens where user='$user' and token='$token'";
        $query = $pdo->prepare($sql);
        $query->execute();
        $row = $query->fetch(PDO::FETCH_ASSOC);
        if ($row) return true;
        return false;
    }
?>