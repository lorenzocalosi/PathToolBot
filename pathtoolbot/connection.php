<?php
$mongoconnection="mongodb://pathtoolbot:pathtoolbot@ds011664.mlab.com:11664/heroku_25t1nfsm"; //The database's URI. It contains my username and password, aswell as the database's name and address.
$connection = new MongoClient($mongoconnection); //We establish a connection
$db  = $connection->heroku_25t1nfsm; //We pick our database
error_log("Connection established"); //We log what happened