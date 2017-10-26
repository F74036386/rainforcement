<?php
main();

function main(){
	$Qt=array();
	$sanit=array();
	$maxRound=500;
	train($Qt,$sanit,$maxRound);

}

function train(&$Qt,&$sanit,$maxRound){
	$reword=1;
	$learnRate=0.5;
	$discountRate=0.9;
	$jp=0.05;
	$board=array();
	for($round=0;$round<$maxRound;$round++){
		echo "<br>round:".$round."<br>";
		$board[0]=iniBoard();
		//echoBoard($board[0]);
		//echo "<br>";
		$board[1]=putCheace($Qt,$sanit,$board[0],'1',$jp); //1,0
		echoBoard($board[1]);
		echo "<br>";
		$board[2]=putCheace($Qt,$sanit,$board[1],'2',$jp);//1,1
		echoBoard($board[2]);
		echo "<br>";
		updateScore($Qt,$sanit,$board[0],$board[2],'1',$learnRate,$discountRate,$reword);
		$step=0;
		for($step=3;$step<10;$step++){
		//	echo $step."<br>";
			if(($step%2)==1){
				$board[$step]=putCheace($Qt,$sanit,$board[$step-1],'1',$jp);
				updateScore($Qt,$sanit,$board[$step-2],$board[$step],'2',$learnRate,$discountRate,$reword);
				$nsn=boardToSName($board[$step],'2');
				if(iswin($nsn,'o')+iswin($nsn,'m')>0){
					updateScore($Qt,$sanit,$board[$step-1],$board[$step],'1',$learnRate,$discountRate,$reword);
					echoBoard($board[$step]);
					echo "<br>";
					//echo "iswin:".iswin($nsn,'o')." ".iswin($nsn,'m')."<br>";
					//echoBoard($nsn);
					break;
				}
			}
			else{
				$board[$step]=putCheace($Qt,$sanit,$board[$step-1],'2',$jp);
				updateScore($Qt,$sanit,$board[$step-2],$board[$step],'1',$learnRate,$discountRate,$reword);
				$nsn=boardToSName($board[$step],'2');
				if(iswin($nsn,'o')+iswin($nsn,'m')>0){
					updateScore($Qt,$sanit,$board[$step-1],$board[$step],'2',$learnRate,$discountRate,$reword);
						echoBoard($board[$step]);
						echo "<br>";
						//echo "iswin:".iswin($nsn,'o')." ".iswin($nsn,'m')."<br>";
						//echoBoard($nsn);
						
						break;
				}
			}
			echoBoard($board[$step]);
			echo "<br>";
		}
		$step--;
		
		updateScore($Qt,$sanit,$board[$step-2],$board[$step],'1',$learnRate,$discountRate,$reword);
	
	}

}


function iniBoard(){
	return "0 0 0 0 0 0 0 0 0";
}

function putCheace($Qt,$sanit,$board,$myColer,$jp){
	$sn=boardToSName($board,$myColer);
	$an=choseNextAction($Qt,$sanit,$sn,$jp);
	$nBoard=toNextBoard($board,$an,$myColer);
	return $nBoard;
}

function toNextBoard($bn,$location,$coler){
	$ctable=mb_split(" ",$bn);
	$ctable[$location]=$coler;
	$ans="";
	for($i=0;$i<9;$i++){
		$ans=$ans.$ctable[$i]." ";
	}
	return $ans;
}

function updateScore(&$Qt,&$sanit,$lastBoard,$nowBoard,$coler,$learnRate,$discountRate,$winRewordNum){
	$lsn=boardToSName($lastBoard,$coler);
	$nsn=boardToSName($nowBoard,$coler);
	$myAn=anaMyAction($lsn,$nsn);
	$mAId=saNameToIndex($Qt,$sanit,$lsn,$myAn);
	$maxScore=getMaxScore($Qt,$nsn);
	$fb=isWin($nsn,"m")-isWin($nsn,"o");
	//echo $fb."<br>";
	$ns=$Qt[$lsn][$mAId]["score"];
	//echo $lsn." <br> ".$mAId."<br>".$ns."<br>".$fb."<br>".$maxScore."<br>";
	$Qt[$lsn][$mAId]["score"]=$ns+$learnRate*($winRewordNum*$fb+$maxScore*$discountRate-$ns);
}

function getMaxScore($Qt,$sn){
	if(isset($Qt[$sn])==0)$c=0;
	else $c=count($Qt[$sn]);
	$max=0;
	for($i=0;$i<$c;$i++){
		if($max<$Qt[$sn][$i])
			$max=$Qt[$sn][$i]["score"];
	}
	if(($c<9)&&($max<0))return 0;
	return $max;
}

function anaMyAction($lsn,$nsn){
	$lsb=mb_split(" ", $lsn);
	$nab=mb_split(" ", $nsn);
	$ma=-1;
	for($i=0;$i<9;$i++){
		if($lsb[$i]!=$nab[$i]){
			//echo "anan".$i."<br>";
			if($nab[$i]=="m"){return $i;}
		}
	}
	return $ma;
}

function choseNextAction($Qt,$snait,$sn,$jp){
	$s=rand(0,10000);	
	if(((double)$s/(double)10000)<=$jp){
		return getRandNextAction($Qt,$snait,$sn);
	}
	return getBestNextAction($Qt,$snait,$sn);
}

function getRandNextAction($Qt,$snait,$sn){
	echo "r<br>";
	while(1){
		$ter=rand()%9;
		$g=isCellEmpty($sn,$ter);
		if($g==1)return $ter;
		//else $tem[$ter]=1;
	}
}

function getBestNextAction($qt,$snait,$sn){
	echo "b<br>";
	if(isset($qt[$sn])==0)$co=0;
	else $co=count($qt[$sn]);
	$maxScore=-3;
	$maxAn=-3;
	//echo " co ".$co."<br>";
	for($i=0;$i<$co;$i++){
	//	echo "i ".$i." maxa ".$qt[$sn][$i]["an"]."scor ".$qt[$sn][$i]["score"]."<br>";
		if($qt[$sn][$i]["score"]>$maxScore){
		//	echo "maxa ".$qt[$sn][$i]["an"]."<br>";
			$maxScore=$qt[$sn][$i]["score"];
			$maxAn=$qt[$sn][$i]["an"];
		}
	}
	if($co==0)return getRandNextAction($qt,$snait,$sn);
	else if(($maxScore<0)&&$co<9)return getRandNextAction($qt,$snait,$sn);
	else{
		//echo "max ".$maxAn."<br>"; 
		return $maxAn;
	}
}

function toNextState($sn,$location,$coler){
	$ctable=mb_split(" ",$sn);
	$ctable[$location]=$coler;
	$ans="";
	for($i=0;$i<9;$i++){
		$ans=$ans.$ctable[$i]." ";
	}
	return $ans;
}

function isCellEmpty($sn,$location){
	$ctable=mb_split(" ",$sn);
	if($ctable[$location]=='0')return 1;
	else return 0;
}

function isfull($sn){
	$ctable=mb_split(" ",$sn);
	$chaceNum=0;
	$co=count($ctable);
	for($i=0;$i<co;$i++){
		if($ctable[$i]!='0')$chaceNum++;
	}
	if($chaceNum==9)return 1;
	if(isWin($sn,'o')==1)return 1;
	if(isWin($sn,'m')==1)return 1;
	return 0;
}

function isWin($sn,$coler){
	$ctable=mb_split(" ",$sn);
	for($i=0;$i<3;$i++){
		if(($ctable[$i*3+0]==$coler)&&($ctable[$i*3+1]==$coler)&&($ctable[$i*3+2]==$coler)){
			return 1;
		}
		if(($ctable[$i+0]==$coler)&&($ctable[$i+3]==$coler)&&($ctable[$i+6]==$coler)){		return 1;	
		}
	}
	if(($ctable[0]==$coler)&&($ctable[4]==$coler)&&($ctable[8]==$coler)){return 1;}
	if(($ctable[2]==$coler)&&($ctable[4]==$coler)&&($ctable[6]==$coler)){return 1;}
	return 0; 
}


function saNameToIndex(&$qt,&$sanit,$sn,$an){
	if(isset($sanit[$sn][$an])==1)return $sanit[$sn][$an];
	if(isset($qt[$sn])==0)$qt[$sn]=array();
	$k=count($qt[$sn]);
	$qt[$sn][$k]["score"]=0;
	$qt[$sn][$k]["an"]=$an;
	$sanit[$sn][$an]=$k;
	//echo "an".$an."<br>";
	return $k;
}

function boardToSName($board,$myColer){
	$bn=mb_split(" ", $board);
	$sn="";
	for($i=0;$i<9;$i++){
		if($bn[$i]==$myColer){
			$sn=$sn."m"." ";
		}
		elseif($bn[$i]=="0"){
			$sn=$sn."0"." ";
		}
		else{
			$sn=$sn."o"." ";	
		}
	}
	return $sn;
}

function echoBoard($bn){
	$bn=mb_split(" ", $bn);
	echo $bn[0]." ".$bn[1]." ".$bn[2]."<br>";
	echo $bn[3]." ".$bn[4]." ".$bn[5]."<br>";
	echo $bn[6]." ".$bn[7]." ".$bn[8]."<br>";
}

function loadData($qt,$sanit){

}
function storeData($qt,$sanit){
	
}
?>