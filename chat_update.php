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

//Read request data and add it to an associative array
$reqData = array();
foreach(explode("&", file_get_contents("php://input")) as $val) {
    $tempData = explode("=",$val);
    $reqData[$tempData[0]] = $tempData[1];
}

$chatid = $reqData["chatId"];
//Validate that user is part of chatroom
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

if (isset($reqData["update"])) {

    $sql1 = "SELECT * from messages where chatroom=:id order by timestamp";
    $query1 = $pdo->prepare($sql1);
    $query1->bindParam(":id", $chatid);
    $query1->execute();
    $row1 = $query1->fetchAll(PDO::FETCH_ASSOC);

    $response = json_encode($row1);
    // foreach ($row1 as $msgRow) {
    //     $response .= '<div class="container cntnr-vertical message-card">
    //     <div class="container message-card-header">
    //         <div class="user-ico-img">
    //         </div>
    //         <div class="message-card-header-details container cntnr-vertical">
    //             <div class="message-card-author">Test User</div>
    //             <div class="message-card-timestamp">3 minutes Ago</div>
    //         </div>
    //     </div>
    //     <div class="message-card-content">
    //         <div class="message-card-text">
    //             <p>
    //                 '.$msgRow["message"].'
    //             </p>
    //         </div>
    //     </div>
    // </div>';
    // }
    echo $response;
}
if (isset($reqData["newMessage"])) {
    $sql2 = "Insert into messages (chatroom, message, user) values (:chat, :msg, :user)";
    $query2 = $pdo->prepare($sql2);
    $query2->bindParam(":chat", $chatid);
    $query2->bindParam(":msg", $reqData["content"]);
    $query2->bindParam(":user", $user);
    $query2->execute();
}

//Feature to be added later
if (isset($reqData["deleteMessage"])) {

}


function verifyToken($pdo, $token, $user) {
    $sql = "SELECT id from tokens where user='$user' and token='$token'";
    $query = $pdo->prepare($sql);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_ASSOC);
    if ($row) return true;
    return false;
}
?>