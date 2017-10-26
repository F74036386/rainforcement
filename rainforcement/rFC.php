<!DOCTYPE html>
<html>
<head>
	<title>Q-learning</title>
</head>
<body>
</body>
</html>


<?php
///Reinforcement Calaulator
main();
function main(){
	$jumpPro=0.0;
	$discountRate = 0.98;
	$learnRate = 0.1;
	$roundMax=100;

/////ini Qtable
	$Qtable=array();
	$i=0;
	for($i=0;$i<16;$i++){
		$Qtable[$i]["left"]["dest"]=$i-1;
		$Qtable[$i]["right"]["dest"]=$i+1;
		$Qtable[$i]["left"]["score"]=0;
		$Qtable[$i]["right"]["score"]=0;
		$Qtable[$i]["left"]["reword"]=0;
		$Qtable[$i]["right"]["reword"]=0;
	}
	//$Qtable[14]["score"]=1;
	$Qtable[14]["right"]["reword"]=1;


	$round=0;
	$step=0;
	/////start learn///
	for($ri=0;$ri<$roundMax;$ri++){
		$nowState=1;
		$step=0;
		while(isfinish($Qtable,$nowState)==0){
		//	echo "<br>nowState:".$nowState."<br>";
			$step++;
			
			$na=getNextAction($Qtable,$nowState,$jumpPro);
			$reword=$Qtable[$nowState][$na]["reword"];
			$nextState=$Qtable[$nowState][$na]["dest"];
			$nna=getBestNextAction($Qtable,$nextState,$jumpPro);
			$nextMaxScore=$Qtable[$nextState][$nna]["score"];
			
			$update =$Qtable[$nowState][$na]["score"]+$learnRate*($reword+$discountRate*$nextMaxScore-$Qtable[$nowState][$na]["score"]);
			//echo "up:".$update."<br>";
			//echo "ns:".$ns."<br>";
			///echo "re:".$reword."<br>";
			//echo "dis:".$discountRate."<br>";
			//echo "learnRate:".$learnRate."<br>";
			//echo "nextMaxScore:".$nextMaxScore."<br>";
			//echo "nextScore:".$Qtable[$ns]["score"]."<br>";
			$Qtable[$nowState][$na]["score"]=$update;
			//echo "nsc ".$Qtable[$nowState]["score"]."<br>";
			
			
			$nowState=$nextState;
			//$step++;
		}
		$round++;
		//showTable($Qtable);
		echo "round: ".$round."  step: ".$step."<br>";
	}
}



function getNextAction($Qtable,$nowState,$jp){
	$s=rand(0,10000);
	
	$ld=$Qtable[$nowState]["left"]["dest"];
	$rd=$Qtable[$nowState]["right"]["dest"];
	if($ld<0)return "right";
	if($rd>15)return"left";

	if(((double)$s/(double)10000)<=$jp){
		$na=rand(0,1);
		//echo"jump<br>";
		if($na==0)return "left";
		else      return "right";
	}
	return getBestNextAction($Qtable,$nowState);
	
}
function getBestNextAction($Qtable,$nowState){
	$ld=$Qtable[$nowState]["left"]["dest"];
	$rd=$Qtable[$nowState]["right"]["dest"];
	if($ld<=0)return "right";
	if($rd>15)return"left";
	if($Qtable[$ld]["left"]["score"]>$Qtable[$rd]["right"]["score"])return "left";
	if($Qtable[$ld]["left"]["score"]<$Qtable[$rd]["right"]["score"])return "right";
	if($Qtable[$ld]["left"]["score"]==$Qtable[$rd]["right"]["score"]){
		$na=rand(0,1);
		if($na==0)return "left";
		else      return "right";
	}
}

function isfinish($Qtable,$nowState){
	if($nowState<15){return 0;}
	else{ return 1;}

}

function showTable($Qtable){
	for($i=0;$i<15;$i++)
		echo"i:".$i." left:".$Qtable[$i]["left"]["score"]." right:".$Qtable[$i]["right"]["score"]."<br>";
}

?>