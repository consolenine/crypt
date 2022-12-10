<?php
session_start();
include "./connection.php";
if (isset($_SESSION['token'])) {
    $token = $_SESSION['token'];
    $user = $_SESSION['user'];

    if (!verifyToken($pdo, $token, $user)) {
        echo "Invalid session token";
        header("Location: logout.php");
        exit(0);
    }
    //Check if user has joined chatrooms
    // $sql = "SELECT chatrooms from user WHERE username = :user";
    // $query = $pdo->prepare($sql);
    // $query->bindParam(":user", $user);
    // $query->execute();
    // $row = $query->fetch(PDO::FETCH_ASSOC);
    // if ($row) {
    //     if ($row["chatrooms"] != "") {
    //         //Redirect to chat page if user is in any chatroom
    //         $activeChats = explode("<>",$row["chatrooms"]);

    //         //Get first chatroom ID
    //         $sql1 = "SELECT id from chatrooms WHERE name = '$activeChats[0]'";
    //         $query1 = $pdo->prepare($sql1);
    //         $query1->execute();
    //         $row1 = $query1->fetch(PDO::FETCH_ASSOC);

    //         // header("Location: chat.php?id=".$row1['id']);
    //         //exit(0);
    //     }
    // }

} else {
    echo "Invalid session token";
    header("Location: logout.php");
    exit(0);
}
if (isset($_POST["create"])) {
    $chatname = $_POST["username"];
    $chatpass = isset($_POST["password"]) ? $_POST["password"] : "";
    if ($chatpass != "") {
        $chatpass = hash('sha256', $chatpass);
    }
    
    $sql_check1 = "Select name from chatrooms where name = :name";
    $query_check1 = $pdo->prepare($sql_check1);
    $query_check1->bindParam(":name", $chatname);
    $query_check1->execute();
    $row_check1 = $query_check1->fetch(PDO::FETCH_ASSOC);

    if ($row_check1) {
        echo "Chat already exists";
        http_response_code(401);
        exit(0);
    }

    $sql1 = "Insert into chatrooms (name, active_users, owner, password) values (:name,:users,:owner,:pass)";
    $query1 = $pdo->prepare($sql1);
    $query1->bindParam(":owner", $user);
    $query1->bindParam(":users", $user);
    $query1->bindParam(":name", $chatname);
    $query1->bindParam(":pass", $chatpass);
    if ($query1->execute()) {
        //Get users currently joined chatroom
        $sql_check2 = "Select * from user where username = :name";
        $query_check2 = $pdo->prepare($sql_check2);
        $query_check2->bindParam(":name", $user);
        $query_check2->execute();
        $row_check2 = $query_check2->fetch(PDO::FETCH_ASSOC);

        if ($row_check2) {
            $chatsIn = $row_check2["chatrooms"] == "" ? $chatname : $row_check2["chatrooms"]."<>".$chatname;
        }

        $sql2 = "Update user set chatrooms=:chats where username=:user";
        $query2 = $pdo->prepare($sql2);
        $query2->bindParam(":chats", $chatsIn);
        $query2->bindParam(":user", $user);
        if ($query2->execute()) {
            echo "Chatroom Created";
            exit(0);
        }
    }

}

if (isset($_POST["join"])) {
    $chatname = $_POST["username"];
    $chatpass = isset($_POST["password"]) ? $_POST["password"] : "";
    if ($chatpass != "") {
        $chatpass = hash('sha256', $chatpass);
    }
    
    $sql_check1 = "Select * from chatrooms where name = :name";
    $query_check1 = $pdo->prepare($sql_check1);
    $query_check1->bindParam(":name", $chatname);
    $query_check1->execute();
    $row_check1 = $query_check1->fetch(PDO::FETCH_ASSOC);
    
    if ($row_check1) {
        if ($row_check1["password"] == $chatpass) {
            //Get users currently joined chatroom
            $sql_check2 = "Select * from user where username = :name";
            $query_check2 = $pdo->prepare($sql_check2);
            $query_check2->bindParam(":name", $user);
            $query_check2->execute();
            $row_check2 = $query_check2->fetch(PDO::FETCH_ASSOC);

            if ($row_check2) {
                if ($row_check2["chatrooms"] == "") {
                    $chatsIn = $chatname;
                } else {
                    $activeChatList = explode("<>", $row_check2["chatrooms"]);
                    if (!in_array($chatname, $activeChatList)) {
                        // echo "Chatroom already joined";
                        // http_response_code(401);
                        // exit(0);
                        array_push($activeChatList, $chatname);
                    }
                    $chatsIn = implode("<>", $activeChatList);
                }
                
                $sql2 = "Update user set chatrooms=:chats where username=:user";
                $query2 = $pdo->prepare($sql2);
                $query2->bindParam(":chats", $chatsIn);
                $query2->bindParam(":user", $user);

                $query2->execute();

                $sql_check3 = "Select * from chatrooms where name = :name";
                $query_check3 = $pdo->prepare($sql_check3);
                $query_check3->bindParam(":name", $chatname);
                $query_check3->execute();
                $row_check3 = $query_check3->fetch(PDO::FETCH_ASSOC);

                if ($row_check3) {
                    //echo "Flag 1";
                    if ($row_check3["active_users"] == "") {
                        $chatsIn = $_SESSION['user'];
                    } else {
                        $activeChatList = explode("<>", $row_check3["active_users"]);
                        if (!in_array($_SESSION["user"], $activeChatList)) {
                            // echo "Chatroom already joined";
                            // http_response_code(401);
                            // exit(0);
                            array_push($activeChatList, $_SESSION["user"]);
                        }
                        $chatsIn = implode("<>", $activeChatList);
                        
                    }
                }
                //echo $chatsIn;
                $sql3 = "Update chatrooms set active_users=:users where name=:name";
                $query3 = $pdo->prepare($sql3);
                $query3->bindParam(":users", $chatsIn);
                $query3->bindParam(":name", $chatname);
                $query3->execute();

                echo "Joined Chatroom";
                exit(0);
                
            }
        }
        echo "Invalid chat credentials.";
        http_response_code(401);
        exit(0);
    }
    echo "Chatroom does not exist";
    http_response_code(401);
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crypt</title>
    <link rel="stylesheet" href="./css/common.css"/>
    <link rel="stylesheet" href="./css/index.css"/>
</head>
<body>
    <div class="container  cntnr-vertical root-container">
        <div class="small-popup">
            <h2></h2>
            <!-- <div class="dot-pulse"></div> -->
        </div>
        <h4 class="heading-txt">Welcome <?php echo $user;?></h4>
        <div class="container cntnr-vertical form-container">
            
            <div class="container form-header">
                <button type="button" class="button button-primary" id="loginFormBtn">Join Existing Chatroom</button>
                <button type="button" class="button button-secondary" id="registerFormBtn">Create New Chatroom</button>
            </div>
            <div class="container form-body">
                <form id="login" class="container cntnr-vertical form" enctype="multipart/form-data">
                    <input type="text" name="username" class="input-field" placeholder="Chatroom ID" required>
                    <input type="password" name="password" class="input-field" placeholder="Password">
                    <p class="error-txt"></p>
                    <button type="submit" id="loginBtn" name="login" class="button button-primary">Join</button>      
                </form>
                <form id="register" class="container cntnr-vertical form">
                    <input type="text" name="username" class="input-field" placeholder="Chatroom Name" required>
                    <input type="password" name="password" class="input-field" placeholder="Enter Password (optional)">
                    
                    <p class="error-txt"></p>
                    <button type="submit" id="registerBtn" name="register" class="button button-primary">Create</button>      
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            

            //Add switchforms event listener
            document.querySelectorAll(".form-header button").forEach(btn => {
                btn.addEventListener('click', function() {
                    switchForms(btn);
                })
            });

            //Add login/register event listeners
            document.querySelector("#login").addEventListener('submit', event => {
                event.preventDefault();
                login(document.querySelector("#login"));
            });
            document.querySelector("#register").addEventListener('submit', event => {
                event.preventDefault();
                register(document.querySelector("#register"));
            });

            function switchForms(caller) {
                var formBody = document.querySelector(".form-body");

                if (caller.id == "loginFormBtn") {
                    document.querySelector("#registerFormBtn").classList.remove("button-primary");
                    document.querySelector("#registerFormBtn").classList.add("button-secondary");
                    caller.classList.remove("button-secondary");
                    caller.classList.add("button-primary");

                    formBody.style.transform = (formBody.style.transform == "translateX(-25%)") ? formBody.style.transform : "translateX(-25%)";
                }
                if (caller.id == "registerFormBtn") {
                    document.querySelector("#loginFormBtn").classList.remove("button-primary");
                    document.querySelector("#loginFormBtn").classList.add("button-secondary");
                    caller.classList.remove("button-secondary");
                    caller.classList.add("button-primary");

                    formBody.style.transform = (formBody.style.transform == "translateX(-75%)") ? formBody.style.transform : "translateX(-75%)";
                }
                document.querySelector(".form-body .error-txt").style.display = "none";
            }

            function login(form) {
                data = new FormData(form);
                data.append("join","");
                const XHR = new XMLHttpRequest();

                XHR.onreadystatechange = function() {
                    var popup = document.querySelector(".small-popup");

                    if (this.readyState == 1) {
                        popup.classList.add("active")
                        popup.children[0].innerHTML = "Logging In";
                    }
                    //On Successfull login
                    if (this.readyState == 4 && this.status == 200) {
                        popup.children[0].innerHTML = "Logged In";
                        popup.classList.remove("active");
                        window.location = "./chat.php";
                    }
                    if (this.readyState == 4 && this.status == 401) {
                        popup.classList.remove("active");
                        document.querySelector("#login .error-txt").style.display = "block";
                        document.querySelector("#login .error-txt").innerHTML = this.responseText;
                        //console.log(this.responseText);
                    }
                }
                
                XHR.open("POST", "./joinchat.php");
                //XHR.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                //data = encodeData(data);
                XHR.send(data);
            }

            function register(form) {
                data = new FormData(form);
                data.append("create","");
                const XHR = new XMLHttpRequest();

                XHR.onreadystatechange = function() {
                    var popup = document.querySelector(".small-popup");
                    if (this.readyState == 1) {
                        popup.classList.add("active")
                        popup.children[0].innerHTML = "Creating Chatroom";
                    }
                    if (this.readyState == 4 && this.status == 200) {
                        popup.children[0].innerHTML = "Chatroom Created";
                        window.location = "./chat.php";
                    }
                    if (this.readyState == 4 && this.status == 401) {
                        popup.classList.remove("active");
                        document.querySelector("#register .error-txt").innerHTML = this.responseText;
                    }
                }
                XHR.open("POST", "./joinchat.php", true);
                XHR.send(data);
            }
        })
    </script>
    
</body>
</html>

<?php

function verifyToken($pdo, $token, $user) {
    $sql = "SELECT id from tokens where user='$user' and token='$token'";
    $query = $pdo->prepare($sql);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);
    if ($row) return true;
    return false;
}

?>