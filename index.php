<?php
include "./connection.php";
session_start();

if (isset($_SESSION['token'])) {
    $token = $_SESSION['token'];
    $user = $_SESSION['user'];

    if (verifyToken($pdo, $token, $user)) {
        header("Location: joinchat.php");
        exit(0);
    } 
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
    <div class="container root-container">
        <div class="small-popup">
            <h2></h2>
            <!-- <div class="dot-pulse"></div> -->
        </div>
        <div class="container cntnr-vertical form-container">
            <div class="container form-header">
                <button type="button" class="button button-primary" id="loginFormBtn">Login</button>
                <button type="button" class="button button-secondary" id="registerFormBtn">Register</button>
            </div>
            <div class="container form-body">
                <form id="login" class="container cntnr-vertical form" enctype="multipart/form-data">
                    <input type="text" name="username" class="input-field" placeholder="User Id" required>
                    <input type="password" name="password" class="input-field" placeholder="Password" required>
                    <div class="input-group">
                        <input type="checkbox" name="remeberPassword" class="checkbox">
                        <label for="rememberPassword">Remember Password</label>
                    </div>
                    <p class="error-txt"></p>
                    <button type="submit" id="loginBtn" name="login" class="button button-primary">Continue</button>      
                </form>
                <form id="register" class="container cntnr-vertical form" enctype="multipart/form-data">
                    <input type="text" name="username" class="input-field" placeholder="User Id" required>
                    <input type="email" name="email" class="input-field" placeholder="Enter e-mail" required>
                    <input type="password" name="password" class="input-field" placeholder="Enter Password" required>
                    <div class="input-group">
                        <input type="checkbox" class="checkbox">
                        <label>I agree to terms & conditions</label>
                    </div>
                    <p class="error-txt"></p>
                    <button type="submit" id="registerBtn" name="register" class="button button-primary">Register</button>      
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
                data.append("login","");
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
                        window.location = "./joinchat.php";
                    }
                    if (this.readyState == 4 && this.status == 401) {
                        popup.classList.remove("active");
                        document.querySelector("#login .error-txt").style.display = "block";
                        document.querySelector("#login .error-txt").innerHTML = this.responseText;
                        //console.log(this.responseText);
                    }
                }
                XHR.open("POST", "./authenticate.php", true);
                XHR.send(data);
            }

            function register(form) {
                data = new FormData(form);
                data.append("register","");
                const XHR = new XMLHttpRequest();

                XHR.onreadystatechange = function() {
                    var popup = document.querySelector(".small-popup");
                    if (this.readyState == 1) {
                        popup.classList.add("active")
                        popup.children[0].innerHTML = "Registering";
                    }
                    if (this.readyState == 4 && this.status == 200) {
                        popup.children[0].innerHTML = "Registered Successfully";
                        window.location = "./joinchat.php";
                    }
                    if (this.readyState == 4 && this.status == 401) {
                        popup.classList.remove("active");
                        document.querySelector("#register .error-txt").innerHTML = this.responseText;
                    }
                }
                XHR.open("POST", "./authenticate.php", true);
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