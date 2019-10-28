# 490-Servers
Servers for 490 project




We have another repository where we tested the scrabble game itself before moving it to the 490 servers repository (https://github.com/suong4554/Scrabble-Service).
  
The dev server has all the project specific updates required of our team, the QA and PROD servers currently are on the midterm version of our deliverables for this class.  
  
Readme's for each specific server can be found under the dev version of each server and the version control server (most of the information on how to set up is seen here).


## **Server IP Configuration**  
##### **Dev Instances:**    
192.168.1.10 - Dev Broker  
192.168.1.7 - Dev DMZ   
192.168.1.2 - Dev Client  
  
##### **QA Instances:**  
192.168.1.30 - QA Broker  
192.168.1.27 - QA DMZ  
192.168.1.22 - QA Client  
  
##### **Prod Instances:**  
192.168.1.20 - Prod Broker
192.168.1.19 - Prod Broker-FailsafeSQL  
192.168.1.17 - Prod DMZ  
192.168.1.16 - Prod DMZ-Failsafe  
192.168.1.12 - Prod Client  
192.168.1.13 - Prod Client-FailsafeSQL  

##### **Version Control:**
192.168.1.50 - Version Control  
  
  

## **Packages to Install (Commands are given)**  
##### **Upgrade**    
sudo apt-get update  
sudo apt-get upgrade   
   
##### **Essentials**    
sudo apt-get install php  
sudo apt-get install php-curl  
sudo apt-get install mysql-server  
sudo apt-get install rabbitmq-server  

##### **System Service**    
sudo apt-get install memcached  

##### **System Version Control**    
sudo apt-get install oppenssh-server openssh-client 
sudo apt-get install sshpass  

 
##### **Failsafe**  
sudo apt-get install ucarp  
sudo apt-get install rsync  
  
  
## **Setting up servers**  
For all servers the "*transfer*" directory will be moved to "*home/transfer*" and the "*html*" directory will be moved to "*/var/www/html*"    
RabbitMQ, along with all code excluding version control and system config files are in the "*/var/www/html*" folder. Version control is in the "*/home/transfer*" directory and system config files are in their respective directories. "*localRabbit.service*" will by in the "*/etc/systemd/system*" folder and for the client, "*apache2.conf*" will be in 
This can be done manually or you can execute the "*move.txt*" file by using the command "*sudo bash move.txt*" (this executes the move.txt file) once each directory is downloaded into their respective machines.
It is assumed that dependencies are already installed and that each server has their respective IP addresses installed.  
Both the Broker and Client server use MYSQL. It is important to create a table called "matches" in the Client server and "userAccounts" in the Broker server with the user "sfu5@localhost" with the password "njit"
  
  
## **Setting up version control**  
In order for version control to work ssh must be allowed to run to the IP addresses that each server needs to connect to.  
The command to do this is to run "*ssh root@InsertIPaddressOfHost*" and enter "*yes*" for the prompt that pops up then disconnect from the host by using "*ctrl d*".
So the Broker of each environment needs to be able to access the Client, Version Control, and DMZ servers.
The Client needs to access the Broker.
The DMZ needs to access the Broker.
The Version Control server needs to have access to the three Brokers for each environment (Broker-Dev, Broker-QA, Broker-Prod)  
 
## **Setting up RabbitMQ as a System Service**  
For all servers the *system* directory will be have a file called *"localRabbit.service"*. This file will go into "*/etc/systemd/system*".
After moving the file, run "*sudo systemctl daemon-reload*" then, "*sudo systemctl enable localRabbit*", this will start the system service on server start.  
This file will assume that there is a file called "*/var/www/html/RabbitMQServer.php*" and that "*RabbitMQServer.php*" can run as a regular php command



## Quick Notes on Each Server:

##### **Client Server:**  
Client Server is where the client connects and logs in.   
From the login or registration page the user is sent to home.php which has the options to *Play Scrabble*, *View Match History*, and to *Logout*  


##### Play Scrabble (Starting a game with a random person, using API to validate words, integrating a chat platform, resuming game state on disconnect)   
If you select *Play Scrabble* you will be redirected to a finding match page which will have an option to *Cancel* the search at any time.  
Once someone else logs in and starts looking for a match too, you will automatically be redirected to the Scrabble Game itself and your match will start (It is important to note that only one player is allowed to play at once, so you will either be redirected to a scrabble board with some stats at the bottom or to another waiting page).
There will be a chat that keeps the 15 most recent messages in the waiting and scrabble pages (The chat is unique per each game, similar to a private chat).  
There is also a timer for 120 seconds that will automatically pass your turn if you fail to end your turn at the end of each turn.  
Ads will display as long as you're browser/wifi accepts the ads. We have added the ads to the home page and loading page as we wanted to keep the game interface clean and simple.   
If you select the logout/quitButton you will be logged out and the game **will delete itself**, meaning no reconnecting and no ending result.  
If you decide to end the game/Declare the winner, the game will calculate the winner but will still **delete the current session**.  
*These were choices made in order to emphasize not quitting in the middle of the game as the other player will be left waiting*  
If your browser closes out mid game (**This will be important to test *resuming a game state on disconnect* **) when you log back in and click on *Play Scrabble* you will be sent back to the Scrabble Game you were currently in.  
  
##### View Match History  
This will simply view match history in the format of a table from data stored in MySQL. 



##### *Testing the Scrabble Game*
In order to test two users logging in at once and playing a scrabble game together, Chrome was installed and ran along side a Firefox (Firefox runs from a single .exe file so multiple private windows will share cookies) window to simulate multiple connections.
  

##### *Testing Version Control*
In order to test version control, log in as sudo into the version control server (192.168.1.50). Once logged in, change directories to the "*/var/www/html/*" folder and type in "*php request.php*". This will display an output of all possible commands which are self explanatory. The commands are:  <br><br>
######COMMANDS#########  
#getDev (Packages Dev environment into the version control (called by "*php request.php getDev*"))    <br><br>
#toDev  (Pushes Version control specified version to Dev (called by "*php request.php toDev &lt;version here>*", if no version is specified, defaults to most recently uploaded version))   <br><br>
#getQA (Packages QA environment into the version control (called by "*php request.php getQA*"))    <br><br>
#toQA (Pushes Version control specified version to QA (called by "*php request.php toQA &lt;version here>*", if no version is specified, defaults to most recently uploaded version))   <br><br>
#getProd (Packages Prod environment into the version control (called by "*php request.php getProd*"))     <br><br>
#toProd (Pushes Version control specified version to Prod (called by "*php request.php toProd &lt;version here>*", if no version is specified, defaults to most recently uploaded version))   <br>
#showVersion (Shows all versions in stored in the version control system and their details such as file location and date created,  "*php request.php showVersion &lt;version here>*")   <br><br>
#deleteVersion (Deletes a version specified, called by "*php request.php deleteVersion &lt;version here>>*")  <br><br>
the "*getxxx*" commands will show the version that it stored the package under once it is completed.
	 


##### **DMZ Server:**  
This server just takes in a request, calls the Words API, and captures the response. 
*You will need to install php-curl in order for it to properly work.*
Each request to this server is an array of words, if a single one of the words is wrong it will return false signifying that the pieces the player put down are invalid.
  
    
	 


##### **SQL/Broker Server:**  
This server acts as both the SQL and Broker.  
This server will take in requests and forward it to the DMZ server or execute certain requests that call on SQL such as authentication/registration. 
It is also configured to work with matchmaking but that is currently not used in order to prevent the server from hanging along with the error we posted in error.png. 



