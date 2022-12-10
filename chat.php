<?php
session_start();
include "./connection.php";

if (isset($_SESSION['token'])) {
    $token = $_SESSION['token'];
    $user = $_SESSION['user'];

    if (!verifyToken($pdo, $token, $user)) {
        header("Location: index.php");
        exit(0);
    } 
} else {
    header("Location: index.php");
    exit(0);
}

$sql_check = "SELECT chatrooms from user where username=:id";
$query_check = $pdo->prepare($sql_check);
$query_check->bindParam(":id", $_SESSION["user"]);
$query_check->execute();
$row_check = $query_check->fetch(PDO::FETCH_ASSOC);
$allChats = explode("<>", $row_check["chatrooms"]);

if (!isset($_GET['id'])) {
    $firstChat = end($allChats);

    $sql_check1 = "SELECT id from chatrooms where name=:id";
    $query_check1 = $pdo->prepare($sql_check1);
    $query_check1->bindParam(":id", $firstChat);
    $query_check1->execute();
    $row_check1 = $query_check1->fetch(PDO::FETCH_ASSOC);
    $firstId = $row_check1["id"];

    header("Location: chat.php?id=".$firstId);
    exit(0);
} else {
    $chatid = $_GET['id'];
}

$sql = "SELECT * from chatrooms where id=:id";
$query = $pdo->prepare($sql);
$query->bindParam(":id", $chatid);
$query->execute();
$row = $query->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $chatMembers = explode("<>", $row["active_users"]);
    if (!in_array($_SESSION["user"], $chatMembers)) {
        echo "You are not a member of this chatroom";
        http_response_code(401);
        exit(0);
    }
    
    $chatOwner = $row["owner"];
    $chatName = $row["name"];
} else {
    echo "Invalid Chat ID";
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
    <title>Crypt - <?php echo $chatName;?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="./css/common.css"/>
    <link rel="stylesheet" href="./css/chat.css">
</head>
<body>
    <div class="container root-container">
        <div class="container chat-container">
            <?php include('./sidebar.php')?>
            <div class="chat-content">
                <div class="container cntnr-vertical panel-content">
                    <div class="container cntnr-vertical panel-content-main">
                        <div class="container cntnr-vertical panel-content-body">
                            <!-- <?php
                            //for ($i = 0; $i < 10; $i++) {
                            ?>
                            <div class="container cntnr-vertical message-card">
                                <div class="container message-card-header">
                                    <div class="user-ico-img">
                                    </div>
                                    <div class="message-card-header-details container cntnr-vertical">
                                        <div class="message-card-author">Test User</div>
                                        <div class="message-card-timestamp">3 minutes Ago</div>
                                    </div>
                                </div>
                                <div class="message-card-content">
                                    <div class="message-card-text">
                                        <p>
                                            Lorem ipsum dolor, sit amet consectetur adipisicing elit. 
                                            Similique tenetur quae iste harum architecto, sed quasi quam perferendis 
                                            maiores illo sequi commodi debitis esse beatae nihil illum ipsa distinctio rem!
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php
                            //}
                            ?>
                             -->
                        </div>
                    </div>
                    <div class="container panel-content-footer">
                        <form id="chatbox" class="container">
                            <!-- <input type="hidden" name="chatId" value="<?php //echo $chatid;?>">
                            <input type="hidden" name="user" value="<?php //echo $_SESSION['user'];?>"> -->
                            <input type="text" name="content" placeholder="Type something to send" autocomplete="off" autocapitalize="on" autofocus required></input>
                            <button type="submit" name="chatAdd" class="card-header-img">
                                <span class="material-symbols-outlined">
                                send
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="background-blur"></div>
        <div id="chatForm" class="background-blur-parent">
            <div class="small-popup">
                <h2></h2>
                <!-- <div class="dot-pulse"></div> -->
            </div>
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
                    <form id="register" class="container cntnr-vertical form" enctype="multipart/form-data">
                        <input type="text" name="username" class="input-field" placeholder="Chatroom Name" required>
                        <input type="password" name="password" class="input-field" placeholder="Enter Password (optional)">
                        
                        <p class="error-txt"></p>
                        <button type="submit" id="registerBtn" name="register" class="button button-primary">Create</button>      
                    </form>
                </div>
            </div>
        </div>
    </div>
    
</body>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        document.querySelector('.background-blur').addEventListener('click', ev => {
            var parent = document.querySelectorAll('.background-blur-parent');
            parent.forEach(el => {
                el.style.display = "none";
            })
            ev.target.style.display = "none";
        })
        document.querySelector('#addChatRoom').addEventListener('click', () => {
            document.querySelector('#chatForm').style.display = "block";
            document.querySelector('.background-blur').style.display = "block";
        })

        document.querySelectorAll(".tooltip-parent").forEach(elem => {
            elem.addEventListener('mouseenter', ev=> {
                var tooltip = elem.querySelector(".tooltip-big");
                tooltip.classList.add("active");
            })
            elem.addEventListener('mouseleave', ev=> {
                var tooltip = elem.querySelector(".tooltip-big");
                tooltip.classList.remove("active");
            })
        });

        document.querySelector("#logOutBtn").addEventListener('click', () => {
            window.location = "logout.php";
        })

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

        document.querySelector("#chatbox").addEventListener('submit', ev => {
            ev.preventDefault();
            var msg = document.querySelector("#chatbox input[name='content']");
            chatUpdate(msg.value);
            msg.value = "";
        })
        //Update Chat Using AJAX
        setInterval(chatUpdate, 1000);

        function chatUpdate(message="") {
            var XHR = new XMLHttpRequest();
            XHR.overrideMimeType("application/json");
            XHR.open("POST", "./chat_update.php", true);

            XHR.onreadystatechange = function() {
                var chatContainer = document.querySelector(".chat-content .panel-content-body");

                //On Successfull Retrieval
                if (this.readyState == 4 && this.status == 200) {
                    //chatContainer.innerHTML = this.responseText;
                    var data = JSON.parse(this.responseText);
                    data.forEach(el => {
                        updateMsgCard(el);
                    });
                }
            }

            if (message != "") {
                var params = "newMessage=true&chatId=<?php echo $chatid;?>&content="+message;
                XHR.send(params);
            } else {
                var params = "update=true&chatId=<?php echo $chatid;?>";
                XHR.send(params);
            }
        }

        function updateMsgCard(data) {
            //console.log(data);
            try {
                var msgCard = document.querySelector(`#msg${data.id}`);
                if (!msgCard) {
                    throw new Exception();
                }
                //console.log("yes");


            } catch (e) {
                //console.log(e.message);
                //console.log("no");
                var div = document.createElement("div");
                div.className = "container cntnr-vertical message-card";
                div.id = "msg"+data.id;
                div.innerHTML = '<div class="container message-card-header">'+
                                '<div class="user-ico-img">'+
                                '</div>'+
                                '<div class="message-card-header-details container cntnr-vertical">'+
                                    '<div class="message-card-author">'+data.user+'</div>'+
                                    '<div class="message-card-timestamp">'+data.timestamp+'</div>'+
                                '</div>'+
                            '</div>'+
                            '<div class="message-card-content">'+
                                '<div class="message-card-text">'+
                                    '<p>'+
                                        data.message;
                                    '</p>'+
                                '</div>'+
                            '</div>';
                document.querySelector(".chat-content .panel-content-body").append(div);
            }

        }

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
                    window.location = "./chat.php";
                }
                if (this.readyState == 4 && this.status == 401) {
                    popup.classList.remove("active");
                    document.querySelector("#login .error-txt").style.display = "block";
                    document.querySelector("#login .error-txt").innerHTML = this.responseText;
                    //console.log(this.responseText);
                }
            }
            XHR.open("POST", "./joinchat.php", true);
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