<div class="panel  sidebar">
    <div class="panel-left">
        <div class="container cntnr-vertical panel-content">
            <div class="container cntnr-vertical panel-content-main">
                <div class="container cntnr-vertical panel-content-body">
                    <?php

                        for ($i = 0; $i < sizeof($allChats); $i++) {
                            $sql2 = "SELECT id from chatrooms where name=:id";
                            $query2 = $pdo->prepare($sql2);
                            $query2->bindParam(":id", $allChats[$i]);
                            $query2->execute();
                            $row2 = $query2->fetch(PDO::FETCH_ASSOC);
                            $currChatId = $row2["id"];
                    ?>
                    <a href="<?php echo 'chat.php?id='.$currChatId?>">
                        <div class="card-header-img <?php echo $currChatId == array_pop(explode('id=', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"))? 'active': ''?>">
                                <span><?php echo $allChats[$i][0].$allChats[$i][-1];?></span>
                        </div>
                    </a>
                    <?php
                        }
                    ?>
                    <div class="card-header-img" id="addChatRoom">
                        <span class="material-symbols-outlined">
                        add
                        </span>
                    </div>
                </div>
            </div>
            <div class="container panel-content-footer">
                <div class="card-header-img tooltip-parent" id="settingsBtn">
                    <div class=" container cntnr-vertical tooltip-big">
                        <h6 id="logOutBtn">LOG OUT</h6>
                        <h6>SETTINGS</h6>
                    </div>
                    <span class="material-symbols-outlined">
                    settings
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="panel-right user-list">
        <div class="container cntnr-vertical panel-content">
            <div class="container cntnr-vertical panel-content-main">
                <div class="panel-content-header">
                    <h4 class="heading-txt"><?php echo $chatName;?></h4>
                </div>
                <div class="container cntnr-vertical panel-content-body">
                    <div class="panel-body-item no-change">
                        <h5 class="subheading-txt">OWNER</h5>
                    </div>
                    <div class="panel-body-item">
                        <h6 class="subheading-txt"><?php echo $chatOwner;?></h6>
                    </div>
                    <div class="panel-body-item no-change">
                        <h5 class="subheading-txt">MEMBERS</h5>
                    </div>
                    <?php
                        for ($i = 0; $i < sizeof($chatMembers); $i++) {
                    ?>
                    <div class="panel-body-item">
                        <h6 class="subheading-txt"><?php echo $chatMembers[$i];?></h6>
                    </div>
                    <?php
                        }
                    ?>
                </div>
            </div>
            <div class="panel-content-footer">
            </div>  
        </div>
    </div>
</div>
<script>
    (function() {
        const settingsBtnn = document.querySelector("#settingsBtn");
        const addChatRoomBtn = document.querySelector("#addChatRoom");

        settingsBtn.addEventListener('click', ev => {
            //ev.target.querySelector("")
        })
    })()
</script>