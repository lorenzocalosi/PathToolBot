<?php
require 'connection.php'; //Connection to MongoDB
require 'functions.php'; //Useful Functions

$content = file_get_contents("php://input"); // This is the variable in which the Telegram message is dumped.
$decoded = json_decode($content, true); // The original JSON Telegram message decoded as an array.

if(!$decoded)//If for some reason the JSON message doesn't decode...
{
  exit;
}
$welcomeText="Welcome! Please type / to view all the commands."; // The welcome text displayed when the user types /start.
$contactText="You can contact me via Telegram @ParadoxJester or send me an email: lorenzo.calosi1@gmail.com"; // The contact text displayed when the user types /contact.
$message = isset($decoded['message']) ? $decoded['message'] : ""; // The message extracted from the original JSON.
$messageId = isset($message['message_id']) ? $message['message_id'] : ""; // The message's ID.
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : ""; // The chat's ID.
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : ""; // The sender's first name.
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : ""; // The sender's last name.
$username = isset($message['chat']['username']) ? $message['chat']['username'] : ""; // The sender's username.
$date = isset($message['date']) ? $message['date'] : ""; // The message's date.
$text = isset($message['text']) ? $message['text'] : ""; // The message's text.

$text = trim($text); //We trim the original text to delete possible spaces.
$text = strtolower($text); //We set the text to lowercase, to work with it better.
$response=""; // The response we're gonna send.
$statusCheck=$db->status->findOne(["chat_id"=>$chatId]); // The chat's current status.

//We analyse the given text.

if($text=="/start"){	
	$response=$welcomeText; //If the user wrote start, we show the welcome text.
}

/*Here are the default rolls. They go as follows:
d4 - Rolls 1 4 faced die.
d6 - Rolls 1 6 faced die.
d8 - Rolls 1 8 faced die.
d10 - Rolls 1 10 faced die.
d12 - Rolls 1 12 faced die.
d20 - Rolls 1 20 faced die.
d100 - Rolls 1 100 faced die.
*/

else if($text=="/d4"){
	reset_status($chatId,$db); //Reset status is called to clear the Bot's memory from any of this user's previous actions.
	$response=roll(4);
}
else if($text=="/d6"){
	reset_status($chatId,$db);//If there's another command going on, we delete its record.
	$response=roll(6);
}
else if($text=="/d8"){
	reset_status($chatId,$db);//If there's another command going on, we delete its record.
	$response=roll(8);
}
else if($text=="/d10"){
	reset_status($chatId,$db);//If there's another command going on, we delete its record.
	$response=roll(10);
}
else if($text=="/d12"){
	reset_status($chatId,$db);//If there's another command going on, we delete its record.
	$response=roll(12);
}
else if($text=="/d20"){
	reset_status($chatId,$db);//If there's another command going on, we delete its record.
	$response=roll(20);
}
else if($text=="/d100"){
	reset_status($chatId,$db);//If there's another command going on, we delete its record.
	$response=roll(100);
}

else if($text=="/contact"){ //Contact info command
	$response=$contactText;
}

else if($text=="/monster"){ //Command used to initiate a search for a monster.
	reset_status($chatId,$db);//If there's another command going on, we delete its record.
	$db->status->insert(["chat_id"=>$chatId,"status"=>"monster"]);//We create a new record, with the chatId as its identifier. The status is now "monster".
	$response="Insert monster's name:";//We ask the user which monster it's looking for.
}

else if($text=="/spell"){ //Command used to initiate a search for a spell.
	reset_status($chatId,$db);//If there's another command going on, we delete its record.
	$db->status->insert(["chat_id"=>$chatId,"status"=>"spell"]);//We create a new record, with the chatId as its identifier. The status is now "spell".
	$response="Insert spell's name:";//We ask the user which spell it's looking for.
}

else if($text=="/trait"){ //Command used to initiate a search for a trait.
	reset_status($chatId,$db);//If there's another command going on, we delete its record.
	$db->status->insert(["chat_id"=>$chatId,"status"=>"trait"]);//We create a new record, with the chatId as its identifier. The status is now "trait".
	$response="Insert trait's name:";//We ask the user which trait it's looking for.
}

else if($text=="/feat"){ //Command used to initiate a search for a feat.
	reset_status($chatId,$db);//If there's another command going on, we delete its record.
	$db->status->insert(["chat_id"=>$chatId,"status"=>"feat"]);//We create a new record, with the chatId as its identifier. The status is now "feat".
	$response="Insert feat's name:";//We ask the user which feat it's looking for.
}

else if($text=="/roll"){ //Command used to initiate a custom roll.
	reset_status($chatId,$db); //If there's another command going on, we delete its record.
	$db->status->insert(["chat_id"=>$chatId,"status"=>"roll"]); //We create a new record, with the chatId as its identifier. The status is now "roll".
	$response="Insert number of die/dice:"; //We ask the user for the number of dice.
}

/*else if($text=="/createItem"){
	reset_status($chatId,$db);
	$db->status->insert(["chat_id"=>$chatId,"status"=>"createitem"]);
	$minorButton = new KeyboardButton('Minor');
	$mediumButton = new KeyboardButton('Medium');
	$majorButton = new KeyboardButton('Major');
	$response="What kind of magic item would you like to create?";
	$item_keyboard= new ReplyKeyboardMarkup([["Minor"=>$minorButton,"Medium"=>$mediumButton,"Major"=>$majorButton]]);
	$itemflag=true;
}*/

else if($statusCheck["status"]=="monster"){ //If the status of the conversation is "monster", it means the user is searching a monster.
	error_log("Monster searched: ".$text); //We log what monster the user searched
	$monster=$db->bestiary->findOne(["Name"=>new MongoRegex("/^".$text."$/i")]); //We search in the database for the monster that the user wants to find.
	if($monster!=null){ //If we find a result:
		error_log("Monster found: ".$monster["Name"]); //We log that we found something
		$response="<b>Name</b>: ".$monster["Name"] //We send to the user the info he needs
		."\n<b>CR</b>: ".$monster["CR"]
		."\n<b>XP</b>: ".$monster["XP"]
		."\n<b>Alignment</b>: ".$monster["Alignment"]
		."\n<b>Size</b>: ".$monster["Size"]
		."\n<b>Type</b>: ".$monster["Type"]
		."\n<b>Hit Die</b>: ".$monster["HD"]
		."\n<b>Average HP</b>: ".$monster["HP"]
		."\n<b>Saving throws</b>"
		."\n<b>Fortitude</b>: ".$monster["Fort"]
		."\n<b>Reflex</b>: ".$monster["Ref"]
		."\n<b>Willpower</b>: ".$monster["Will"]
		."\n<b>Ability Scores</b>"
		."\n<b>Strength</b>: ".$monster["Str"]
		."\n<b>Dexterity</b>: ".$monster["Dex"]
		."\n<b>Costituition</b>: ".$monster["Con"]
		."\n<b>Intelligence</b>: ".$monster["Int"]
		."\n<b>Wisdom</b>: ".$monster["Wis"]
		."\n<b>Charisma</b>: ".$monster["Cha"]
		."\n<b>Attacks</b>"
		."\n<b>Melee</b>: ".$monster["Melee"]
		."\n<b>Ranged</b>: ".$monster["Ranged"]
		."\n<b>Other</b>"
		."\n<b>Feats</b>: ".$monster["Feats"]
		."\n<b>Skills</b>: ".$monster["Skills"]
		."\n<b>Racial Modifiers</b>: ".$monster["RacialMods"]
		."\n<b>Languages</b>: ".$monster["Languages"]
		."\n<b>Source</b>: ".$monster["Source"];
	}
	else{
		$response="Monster not found!"; //We tell the user that we didn't find the monster.
	}
	reset_status($chatId,$db); //We reset the chat's status.
}

else if($statusCheck["status"]=="spell"){ //If the status of the conversation is "spell", it means the user is searching a spell.
	error_log("Spell searched: ".$text); //We log what spell the user searched
	$spell=$db->spells->findOne(["name"=>new MongoRegex("/^".$text."$/i")]); //We search in the database for the spell that the user wants to find.
	if($spell!=null){ //If we find a result:
		error_log("Spell found: ".$spell["name"]); //We log that we found something
		$response="<b>Name</b>: ".$spell["name"] //We send to the user the info he needs
		."\n<b>School</b>: ".$spell["school"]
		."\n<b>Subschool</b>: ".$spell["subschool"]
		."\n<b>Spell Level</b>: ".$spell["spell_level"]
		."\n<b>Casting time</b>: ".$spell["casting_time"]
		."\n<b>Components</b>: ".$spell["components"]
		."\n<b>Range</b>: ".$spell["range"]
		."\n<b>Area</b>: ".$spell["area"]
		."\n<b>Effect</b>: ".$spell["effect"]
		."\n<b>Targets</b>: ".$spell["targets"]
		."\n<b>Duration</b>: ".$spell["duration"]
		."\n<b>Saving Throw</b>: ".$spell["saving_throw"]
		."\n<b>Spell Resistance</b>: ".$spell["spell_resistance"]
		."\n<b>Short Description</b>: ".$spell["short_description"]
		."\n<b>Description</b>: ".$spell["description"]
		."\n<b>Source</b>: ".$spell["source"];
	}
	else{
		$response="Spell not found!"; //We tell the user that we didn't find the spell.
	}
	reset_status($chatId,$db); //We reset the chat's status.
}

else if($statusCheck["status"]=="trait"){ //If the status of the conversation is "trait", it means the user is searching a trait.
	error_log("Trait searched: ".$text); //We log what trait the user searched
	$trait=$db->traits->findOne(["Name1"=>new MongoRegex("/^".$text."$/i")]); //We search in the database for the trait that the user wants to find.
	if($trait!=null){
		error_log("Trait found: ".$trait["Name1"]); //We log that we found something
		$response="<b>Name</b>: ".$trait["Name1"] //We send to the user the info he needs
		."\n<b>Type</b>: ".$trait["Type"]
		."\n<b>Category</b>: ".$trait["Category"]
		."\n<b>Required Race</b>: ".$trait["Req-Race1"]
		."\n<b>Required Class</b>: ".$trait["Req-Class"]
		."\n<b>Required Alignment</b>: ".$trait["Req-Align"]
		."\n<b>Other Requisites</b>: ".$trait["Req-Other"]
		."\n<b>Description</b>: ".$trait["Description"]
		."\n<b>Source</b>: ".$trait["Source"]
		."\n<b>URL</b>: ".$trait["URL"];
	}
	else{
		$response="Trait not found!"; //We tell the user that we didn't find the trait.
	}
	reset_status($chatId,$db);//We reset the chat's status.
}

else if($statusCheck["status"]=="feat"){ //If the status of the conversation is "feat", it means the user is searching a feat.
	error_log("Feat searched: ".$text); //We log what feat the user searched
	$feat=$db->feats->findOne(["name"=>new MongoRegex("/^".$text."$/i")]); //We search in the database for the feat that the user wants to find.
	if($feat!=null){
		error_log("Feat found: ".$feat["name"]); //We log that we found something
		$response="<b>Name</b>: ".$feat["name"] //We send to the user the info he needs
		."\n<b>Type</b>: ".$feat["type"]
		."\n<b>Prerequisites</b>: ".$feat["prerequisites"]
		."\n<b>Prerequisite Feats</b>: ".$feat["prerequisite_feats"]
		."\n<b>Benefits</b>: ".$feat["benefit"]
		."\n<b>Description</b>: ".$feat["description"]
		."\n<b>Source</b>: ".$feat["source"];
	}
	else{
		$response="Feat not found!"; //We tell the user that we didn't find the feat.
	}
	reset_status($chatId,$db);//We reset the chat's status.
}

else if($statusCheck["status"]=="roll"){ //If the status of the conversation is "roll", it means the user has inputted how many dice it wants to throw.
	$dieroll=intval($text); //We transform the text into an int, to check if it's actually a number. If it's not, it will output 0.
	if($dieroll<1||$dieroll>10000){ //If the number of die is less than 1, the input is either 0, a negative number, or text, and therefore not valid.
		$response="Not valid!";
		reset_status($chatId,$db);
	}
	else{
		$db->status->findAndModify(["chat_id"=>$chatId],['$set'=>["status"=>"quantitySelected","quantity"=>$dieroll]]); //We update the status to quantitySelected.
		$response="How many faces does your die/dice have?"; //We ask the user for the dice's faces.
	}
}

else if($statusCheck["status"]=="quantitySelected"){ //If the status is quantitySelected, the user already told us how many dice it wants to throw.
	$dieface=intval($text); //We transform the text into an int, to check if it's actually a number. If it's not, it will output 0.
	if($dieface<1||$dieface>10000){ //If the number of faces is less than 1, the input is either 0, a negative number, or text, and therefore not valid.
		$response="Not valid!";
		reset_status($chatId,$db);
	}
	else{
		$response=roll($dieface,$statusCheck["quantity"]); //We run the roll function and send the output as a message.
		reset_status($chatId,$db); //We clear the status.
	}
}

else if($statusCheck["status"]=="createItem"){
	if($text=="Minor"){
		$itemroll=roll(100);
		if($itemroll<=4){
			$itemtype="armorshield";
		}
		else if($itemroll<=9){
			$itemtype="weapon";
		}
		else if($itemroll<=44){
			$itemtype="potion";
		}
		else if($itemroll<=46){
			$itemtype="ring";
		}
		else if($itemroll<=81){
			$itemtype="scroll";
		}
		else if($itemroll<=91){
			$itemtype="wand";
		}
		else if($itemroll<=100){
			$itemtype="wondrous";
		}
		error_log("Minor item; Item roll: ".$itemroll."; Item type: ".$itemtype);
	}
	else if($text=="Medium"){
		$itemroll=roll(100);
		if($itemroll<=10){
			$itemtype="armorshield";
		}
		else if($itemroll<=20){
			$itemtype="weapon";
		}
		else if($itemroll<=30){
			$itemtype="potion";
		}
		else if($itemroll<=40){
			$itemtype="ring";
		}
		else if($itemroll<=50){
			$itemtype="rod";
		}
		else if($itemroll<=65){
			$itemtype="scroll";
		}
		else if($itemroll<=68){
			$itemtype="stave";
		}
		else if($itemroll<=83){
			$itemtype="wand";
		}
		else if($itemroll<=100){
			$itemtype="wondrous";
		}
		error_log("Medium item; Item roll: ".$itemroll."; Item type: ".$itemtype);
	}
	else if($text=="Major"){
		$itemroll=roll(100);
		if($itemroll<=10){
			$itemtype="armorshield";
		}
		else if($itemroll<=20){
			$itemtype="weapon";
		}
		else if($itemroll<=25){
			$itemtype="potion";
		}
		else if($itemroll<=35){
			$itemtype="ring";
		}
		else if($itemroll<=45){
			$itemtype="rod";
		}
		else if($itemroll<=55){
			$itemtype="scroll";
		}
		else if($itemroll<=75){
			$itemtype="stave";
		}
		else if($itemroll<=80){
			$itemtype="wand";
		}
		else if($itemroll<=100){
			$itemtype="wondrous";
		}
		error_log("Major item; Item roll: ".$itemroll."; Item type: ".$itemtype);
	}
}

else{ //The user's input is unknown.
	$response="Unknown Command!";
}

header("Content-Type: application/json"); //We prepare the response.
$parameters = array('chat_id' => $chatId, 'text' => $response); //We prepare the parameters of the message.
$parameters["method"] = "sendMessage"; //We prepare the method of our response.
$parameters["parse_mode"] = "HTML"; //We set the parseMode as HTML, to use bold text.
if($itemflag==true){
	$parameters["reply_markup"]=$item_keyboard;
}
echo json_encode($parameters); //We encode the text as a JSON.
