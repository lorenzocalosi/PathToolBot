<?php
	function roll ($maxFace, $dieNum=1){ //This function is used to throw dice. $maxFace is the type of die, $dieNum is the number of dice.
		error_log("Rolling ".$dieNum."d".$maxFace); //We log what dice we roll and how many of them.
		$result=0; //This is the roll's result
		for($i=0;$i<$dieNum;$i++){ //We loop for as many die as we need to throw.
			$current_roll=mt_rand(1,$maxFace); //Each roll is decided by the PHP function mt_rand. The first parameter is the minimum, the second one is the maximum.
			error_log("Rolled a ".$current_roll."!"); //We log each roll
			$result+=$current_roll; //We add this roll to the result.
		}
		if($dieNum==1){
			$die="die"; //If it's only one die it's a die
		}
		else{
			$die="dice"; //If it's more than one it's dice
		}
		$returnString = "You rolled ".$dieNum." ".$maxFace."-faced ".$die." for a result of: <b>".$result."</b>"; //We return the results to the user.
		return $returnString;
	}

	function reset_status($chatId,$db){ //We use this function to reset the chat's status.
		error_log("Deleting record: ".$chatId); //We log each delete.
		$db->status->remove(["chat_id"=>$chatId]); //We remove the status from our database.
	}