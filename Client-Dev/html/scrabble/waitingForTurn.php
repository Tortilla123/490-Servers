<?php
//Wanted to note that SQL is called locally since this is basically used as a cache for only matchmaking. 
//If we continuously call rabbitmq in a while loop to send and receive message, we end up "clogging" the queue causing it to freeze
//Thus we call SQL locally by design choice, not because we did not know how to code this step

session_start();

include("../account.php");
include("matchmaking/Function.php");


error_reporting(E_ALL);
ini_set('display_errors',on);




//testing
//$_SESSION["login"] = True;
//$_SESSION["user"]= "Bill";


#################################Initiates Connection to SQL SERVER################################
$db = mysqli_connect($servername, $username, $password , $project);
if (mysqli_connect_errno())
  {
	  $message = "Failed to connect to MySQL: " . mysqli_connect_error();
	  echo $message;
	  error_log($message);
	  exit();
  }

mysqli_select_db( $db, $project );
###################################################################################################




if((!isset($_SESSION["login"])) or (!$_SESSION["login"])){
	redirect("", "index.php", 0);
	exit();
}
else{
	$user = $_SESSION["user"];
	$gameID = findInfo($user, "Matchid");
	while($gameID == null){
		$gameID = findInfo($user, "Matchid");
	}
	$_SESSION["gameID"] = $gameID;
	//print($user);
}


?>
<html>

<head>
<title>Scrabble Home</title>

<script src="../libraries/jquery-3.3.1.min.js"></script>
<!--
<link rel="stylesheet" href="../libraries/bootstrap/css/bootstrap.min.css">
<script src="../libraries/bootstrap/js/bootstrap.min.js"></script> -->


</head>

<style>
.tooltip {
  position: relative;
  display: inline-block;
  border-bottom: 1px dotted black; /* If you want dots under the hoverable text */
}

/* Tooltip text */
.tooltip .tooltiptext {
  visibility: hidden;
  width: 120px;
  bottom: 100%;
  left: 50%;
  margin-left: -20px;
  background-color: gray;
  color: #fff;
  text-align: center;
  padding: 5px 0;
  border-radius: 6px;
 
  /* Position the tooltip text - see examples below! */
  position: absolute;
  z-index: 1;
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
  visibility: visible;
}
#ScrabbleContainer{
	width:610px;
	height:610px;
	min-width: 610px;
	min-height: 610px;
	border: 2px solid white;
	border-radius:5px;
}

#ScrabbleBoard input{
	width:40px;
	height:40px;
	outline: 2px solid #808080;
    font-family: arial;
    font-size: 26px;
    
    letter-spacing: 6px;
	text-transform:uppercase;
}

.normalText{
	background-color: #F5F5DC;

}
.DWS{
	background-color: #FFB6C1;
}

.TWS{
	background-color: #CD5C5C;

}
.TLS{
	background-color: #1E90FF;

}
.DLS{
	background-color: #87CEFA;

}

#pieceContainer{
	padding-top: 5px;
	padding-left: 135px;
	max-width:610px
	min-width: 610px;
	min-height: 95px;
	border:2px solid white;
	border-radius:5px;
	
}

#pieceContainer input{
	width:40px;
	height:40px;
	outline: 2px solid #808080;
    font-family: arial;
    font-size: 26px;
    
    letter-spacing: 6px;
	text-transform:uppercase;
}


#ChatBig{
height:auto;
min-height:35px;
min-width:200px;
max-height:35px;
}

#ChatMessages{
height:auto;
color: #000000;
border:solid #dedede;
background-color: #fefefe;
border-radius:5px;
padding:10px;
margin:10px 0;
min-height:200px;
min-width:230px;
max-width:230px;
position: sticky;    
}

#ChatMessages::after{
content:"";
clear:both;
display:table;
position: sticky;    
}

.Info label {
display: inline-block;
width:140px;
text-align:left;

}

#body{
font-family: arial;
font-size:15px;
}

.column{
	float: left;
}
.game{
	min-width:650px;
	min-height: 1080px;
	width:650px;
	height: 1080px;
}
.stats{
	width:250px;

}

</style>

<body onload="init()" id="body" style="min-height:750px;min-width:1080px">
	<div class="container column game" id="Game Interface" style="padding-left:40px;">
<!--

			<div class="container" id="Buttons" style="padding-bottom:20px; padding-top:5px">			
				<div class="btn-group" class="mx-auto" role="group" aria-label="Buttons">
					<button type="button" id="clearCookies" class="btn btn-warning" onClick="logOut()">Log out/Quit Game</button>
				
					<button type="button" id="endGameRedirect" class="btn btn-danger"  onClick="endGame()">End Game/Declare Winner</button>


				</div>
			</div>	
			
-->		   
			<h5 class="card-title">Waiting for Player to finish his turn</h5>

			<div class="container"  id="ScrabbleContainer"> </div>

			<!--<div class="container" id="Buttons" style="padding-top:10px">			
				<button type="button" id="turnEnd" class="btn btn-light" onClick="turnEnd(board, origboard, turn, pieces, playerPieces)">End Turn</button>
				<button type="button" id="pass" class="btn btn-secondary" onClick="pass(board, origboard, turn, pieces, playerPieces)">Pass (skips your turn)</button>
			</div>-->	
			<div class="container" id="pieceContainer" style="min-width:610px;width:610px;max-width:610px;padding-top:30px;"> </div> 	   
			
	</div>
	<div class="column stats">
		<div class="row">
			<div class="container" id="Info"> 


						<label for="timer">Time Remaining</label><br><input type="text" class="form-control" id="timer" readonly></input>
						<br><br>

						

						<label for="user">User:</label><br><input type="text" class="form-control" id="user" readonly></input>
						<br><br>



						<label for="user2">Opponent:</label><br><input type="text" class="form-control" id="user2" readonly></input>
						<br><br>



						<label for="scoreHolder">User Score:</label><br><input type="text" class="form-control" id="scoreHolder" readonly></input>
						<br><br>



						<label for="user2scoreHolder">Opponent Score:</label><br><input type="text" class="form-control" id="user2scoreHolder" readonly></input>


			</div>
		</div>
		
		<br><br><br>
		<h3>Chat Log</h3>
		<div class="container" id="Chat">
			<div id="ChatMessages">
			</div>
	
			<div id="ChatBig" class="form-group"> 
				<label for="ChatText">Enter Message</label>
				<textarea class="form-control z-depth-1" rows="7" id="ChatText" name="ChatText" placeholder="Enter Message" style="max-height:25px;min-width:230px"></textarea>
			</div>

		</div>
	</div>
<center><script data-cfasync="false" type="text/javascript" src="http://www.onclickmega.com/a/display.php?r=2376815"></script></center>

</body>

<script>
function init(){
	//Draws board
	gameID = "<?php  print $gameID; ?>"
	var result = fetchFile("python/" + gameID + "old.json")
	board = result["board"]
	htmlBoard = redrawBoard(board)
	document.getElementById("ScrabbleContainer").innerHTML = htmlBoard
	
	
	//Sets the labels
	user = "<?php print $user; ?>"
	user2 = getOtherUser()
	score2 = getUserScore(user2)
	score = getUserScore(user)
	document.getElementById("user").value = user;
	document.getElementById("scoreHolder").value = score.toString()
	document.getElementById("user2scoreHolder").value = score2.toString()
	document.getElementById("user2").value = user2;
	
	

	//turn=getUserTurn(user2)		
	//document.getElementById("turnCount").value = turn.toString()

	
	
	//Should be a boolean so it will return true or false 
	
	//InitiateSearch was executed in php segment of code
	interval = setInterval(checkFinish, 1000)
	setInterval(timeCheck, 999);
	
	
}
function getOtherUser(){
	var otherUser = ""
	$.ajax({
		url: 'matchmaking/executeFunction.php',
		type: 'POST',
		async: false,
		data:{fName:"getOtherUserinGame", user1:user},
		beforeSend: function() {
			console.log("Getting other User")
		},
		fail: function(xhr, status, error) {
			alert("Error Message:  \r\nNumeric code is: " + xhr.status + " \r\nError is " + error);
		},
	
		success: function(result) {
			console.log("other user is:" + result)
			otherUser = result;
		}
	});	
	//returns the username of the other user looking for a match
	return otherUser
}
function getUserScore(user){
	var score
	$.ajax({
		url: 'matchmaking/executeFunction.php',
		type: 'POST',
		async: false,
		data:{fName:"getUserScore", user1:user},
		beforeSend: function() {
			console.log("Getting User Score")
			//$("#centerloader").addClass("loader");
		},
		fail: function(xhr, status, error) {
			alert("Error Message:  \r\nNumeric code is: " + xhr.status + " \r\nError is " + error);
		},
	
		success: function(result) {
			//$("#centerloader").removeClass("loader");
			console.log( user + "'s score is:" + result)
			score = result;
		}
	});	
	//returns the username of the other user looking for a match
	return score
}
function getUserTurn(user){
	var Turn
	$.ajax({
		url: 'matchmaking/executeFunction.php',
		type: 'POST',
		async: false,
		data:{fName:"findInfo", user:user, information:"turn"},
		beforeSend: function() {
			console.log("Getting User Turn")
			//$("#centerloader").addClass("loader");
		},
		fail: function(xhr, status, error) {
			alert("Error Message:  \r\nNumeric code is: " + xhr.status + " \r\nError is " + error);
		},
	
		success: function(result) {
			//$("#centerloader").removeClass("loader");
			console.log( user + "'s Turn is:" + result)
			Turn = result;
		}
	});	
	//returns the username of the other user looking for a match
	return score
}
function timeCheck(){
	$.ajax({
		url: 'matchmaking/executeFunction.php',
		type: 'POST',
		async: false,
		data:{fName:"getTime", gameID:gameID},
		beforeSend: function() {
			console.log("Getting Time")
		},
		fail: function(xhr, status, error) {
			alert("Error Message:  \r\nNumeric code is: " + xhr.status + " \r\nError is " + error);
		},
		success: function(result) {
			timer = (result);
			document.getElementById("timer").value = timer;
			console.log("Retrieved Time")
		}
	});
	
}

function fetchFile(filename){
	console.log("fetchFile Function")
	
	var temp = ""
	$.ajax({
	type:'POST',
	async: false,
	url: "fetchStats.php",
	data: {file:filename},
	dataType: "json"
	})
	.done(function(msg){
		console.log("succesfully retrieved User data");
		//console.log(msg);
		temp = msg;
	})
	.fail(function(msg){
		console.log("failed to retrieve User data");
		console.log(msg);
		
	});
	return temp
}
function redrawBoard(board){
	console.log("redrawBoard Function")
	var temp = ""
	$.ajax({
		type:'POST',
		async: false,	
		url: "redrawBoardWait.php",
		data: {board1: board},
		dataType: "text"
		
	})
	.done(function(msg){
		console.log("succefully drew board" + msg);
		document.getElementById("ScrabbleContainer").innerHTML = msg;
		temp = msg;
		
	})
	.fail(function(msg){
		console.log("failed to draw board");
		console.log(msg);
	});
	return temp;
}
function checkFinish(){
	temp = checkTurnPriority()
	console.log(temp)
	if(temp == true){
		clearInterval(interval);
		window.location.replace("scrabbleGame.php");
	}
}

function checkTurnPriority(){
	var turnPriority = false
	$.ajax({
		url: 'matchmaking/executeFunction.php',
		type: 'POST',
		async: false,
		data:{fName:"discoverPriority", user1:user},
		beforeSend: function() {
			//$("#centerloader").addClass("loader");
			console.log("Checking turn priority")
		},
		fail: function(xhr, status, error) {
			alert("Error Message:  \r\nNumeric code is: " + xhr.status + " \r\nError is " + error);
		},
	
		success: function(result) {
			//$("#centerloader").removeClass("loader");
			console.log("turn priority: " + result )
			turnPriority = (result == 'true');
		}
	});	
	//returns true if a match is found, otherwise returns false
	console.log("turn priority: " + turnPriority)
	return turnPriority
}


function showPieces(playerPieces){
	console.log("showPieces Function")
	var piecesHTML = ""
	for (var i =0; i < playerPieces.length; i ++){
		piecesHTML += "<input type='text' id='" + i +  "showPieceChar' maxlength='1' class='showPieceChar' value='" + playerPieces[i][0] + "' readonly />";
	}
	piecesHTML += "<br>"
	for (var i =0; i < playerPieces.length; i ++){
		var score = determineScore(playerPieces[i][0])
		var tempSize = ""
		if (score === "10"){
			tempSize = "fontSize='10px'"
			
		}
		piecesHTML += "<input type='text' id='" + i +  "showPieceValue' maxlength='2' class='showPieceChar' value='" + score + "' " + tempSize + "readonly />";
	}
	
	document.getElementById("pieceContainer").innerHTML = piecesHTML
	
	
}

$(document).ready(function() {
	$("#ChatText").keyup(function(e){
			if(e.keyCode == 13) {
					
				var ChatText = $("#ChatText").val();
				$.ajax({
					type:'POST',
					url:'chat/insertMessage.php',
					data:{ChatText:ChatText},
					success:function()
					{
						$("#ChatText").val("");
					
					},
					fail:function()
					{
					alert('request failed');
					}

				})
			}
	})
	
	setInterval(function(){
			$("#ChatMessages").load("chat/DisplayMessages.php");
	},1500)
	
	$("#ChatMessages").load("chat/DisplayMessages.php");
	
});


</script>







</html>
