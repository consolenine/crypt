<?php
include ("./connection.php");

if (isset($_POST["login"])) {
    if (isset($_POST["username"]) && $_POST["password"]) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $stayLoggedIn = isset($_POST["rememberPassword"]) ? true : false;
    }
    else {
        echo "Please fill all required details";
        http_response_code(401);
    }
    try {
        $sql1 = "SELECT * from user where username=:username";
        $query1 = $pdo->prepare($sql1);
        $query1->bindParam(":username", $username);
        $query1->execute();
        $row1 = $query1->fetch(PDO::FETCH_ASSOC);
        if ($row1) {
            $password_check = $row1["password"];
            if (hash('sha256',$password) == $password_check) {
                createLoginSession($pdo, $username);
                echo "Logged In";
                exit(0);

            } else {
                echo "Invalid user/password";
                http_response_code(401);
            }
        } else {
            throw new Exception("Invalid user/password");
        }
    } catch(Exception $e) {
        echo $e->getMessage();
        http_response_code(401);
    }


}
if (isset($_POST["register"])) {
    if (isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["email"])) {
        $username = $_POST["username"];
        $password = $_POST["password"];
        $email = $_POST["email"];
    }
    else {
        echo "Please fill all required details";
        http_response_code(401);
    }

    try {
        $sql1 = "SELECT * from user where username=:username";
        $query1 = $pdo->prepare($sql1);
        $query1->bindParam(":username", $username);
        $query1->execute();
        $row1 = $query1->fetch(PDO::FETCH_ASSOC);
        //If user already exists
        if (!$row1) {
            $password = hash('sha256', $password);

            $sql2 = "Insert into user (username, email, password) values (:username,:email,:password)";
            $query2 = $pdo->prepare($sql2);
            $query2->bindParam(":username", $username);
            $query2->bindParam(":email", $email);
            $query2->bindParam(":password", $password);
            if ($query2->execute()) {
                if (createLoginSession($pdo, $username)) {
                    echo "Registered successfully.";
                } else {
                    echo "Server Error";
                    http_response_code(500);
                }
            }

        } else {
            throw new Exception("Invalid user/password");
        }
    } catch(Exception $e) {
        echo $e->getMessage();
        http_response_code(401);
    }   
}

function createLoginSession($pdo, $user) {
    session_start();

    $token = bin2hex(random_bytes(20));
    //Create user token and store in database
    $sql = "SELECT user from tokens where user=:user1";
    $query = $pdo->prepare($sql);
    $query->bindParam(":user1", $user);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $sql1 = "Insert into tokens (user, token) values (:user, :token)";
        $query1 = $pdo->prepare($sql1);
        $query1->bindParam(":user", $user);
        $query1->bindParam(":token", $token);
        if ($query1->execute()) {
            $_SESSION['token'] = $token;
            $_SESSION['user'] = $user;
            return true;
        } else {
            return false;
        }
    } else {
        if ($row["user"] == $user) {
            $_SESSION['token'] = $token;
            $_SESSION['user'] = $user;
            return true;
        }
        return false;
    }
}
?>