<?php

$msqli = file_get_contents('mysql.ini');
$arr_msql = explode('
', $msqli);
foreach ($arr_msql as $key => $value) {
	$arr_swap = explode('=', $value);
	$arr_msql[$key] = $arr_swap[1];
}


$A = new Drive2_auto;
print $arr_msql[0].' , '.$arr_msql[1].' , '.$arr_msql[2].' , '.$arr_msql[3];
//$A->Choice_Base($arr_msql[0], $arr_msql[1], $arr_msql[2], $arr_msql[3], 'parser');

if(isset($_POST['seach_marks']))
	$proces_marks = $_POST['seach_marks'];
else $proces_marks = 'm';
if(isset($_POST['seach_modells']))
	$proces_modells = $_POST['seach_modells'];
else $proces_modells = 'm';

if(isset($_POST['proces_all']) && $_POST['proces_all'] == 1){
	$A->GetBase($proces_marks, $proces_modells);
}
if(isset($_POST['proces_all']) && $_POST['proces_all'] == 2){
	$A->GetInfo($proces_marks, $proces_modells);
}

$html_str = file_get_contents("https://www.drive2.ru/cars/?all"); 

$marks = array();

preg_match_all('#<a[^>]+?class\s*?=\s*?["\']c-link c-link--text["\'][^>]+?href=["\'](.+?)["\'][^>]*?>(.+?)</a>#su', $html_str, $marks);
$count_marks = count($marks[1]);


$result = '<form action="" method="post">';

$result .= '<p><select name="seach_marks"><option selected value="m">Какая марка вас интересует?</option>';
for ($key_mark = 2; $key_mark < $count_marks; ++$key_mark){
	if($marks[1][$key_mark] == $proces_marks)
		$result .= '<option selected value="'.$marks[1][$key_mark].'">'.$marks[2][$key_mark].'</option>';
	else
		$result .= '<option value="'.$marks[1][$key_mark].'">'.$marks[2][$key_mark].'</option>';
}
$result .= '</select>';


$result .=	'	<p><input type="submit" value="модели"></p>';

if(isset($_POST['seach_marks'])){
	
	$html_str_cars = file_get_contents('https://www.drive2.ru'.$proces_marks);
	$modells = array();
	preg_match_all('#<a[^>]+?class\s*?=\s*?["\']c-link c-link--text["\'][^>]+?href=["\'](.+?)["\'][^>]*?>(.+?)</a>#su', $html_str_cars, $modells);
	$count_modells = count($modells[1]);
	
	$result .= '<p>'.$proces_marks.'</p>';
	$result .= '<p><select name="seach_modells"><option selected value="m">Какая модель вас интересует?</option>';
	for($key_modell = 1; $key_modell < $count_modells; ++$key_modell){
		$result .= '<option value="'.$modells[1][$key_modell].'">'.$modells[2][$key_modell].'</option>';
	}
	$result .= '</select>';
	
}

	$result .= '
			<p><select name="proces_all">
			<option selected value="m">Чего вы хотите от этой программы?</option>
			<option value="1">хотите вывести информацию из базы данных?</option>
			<option value="2">хотите спарсить информацию с сайта?</option>
			</select></p>';

$result .=	'	<p><input type="submit" value="Отправить запрос"></p>
			</form>';

print $result;

class Drive2_auto { 
	private $__host = 'localhost'; 
	private $__user = 'root'; 
	private $__passw = ''; 
    private $__base = 'drive2-auto'; 
    private $__table = 'parser'; 
	
	public function Choice_Base($host, $user, $passw, $base, $table){
		$this->__host = $host;
		$this->__user = $user;
		$this->__passw = $passw;
		$this->__base = $base; 
		$this->__table = $table; 
	}
    
    public function GetInfo($link_marks, $link_modells) {
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, "example.com"); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$output = curl_exec($ch); 

		set_time_limit ( 3000 );

		$link = mysqli_connect($this->__host, $this->__user, $this->__passw, $this->__base);

		$query = 'OPEN TABLE '.$this->__table.')';
		mysqli_query($link, $query);


		curl_setopt($ch, CURLOPT_URL, "https://www.drive2.ru/cars/?all"); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$html_str = curl_exec($ch);

		$marks = array();

		preg_match_all('#<a[^>]+?class\s*?=\s*?["\']c-link c-link--text["\'][^>]+?href=["\'](.+?)["\'][^>]*?>(.+?)</a>#su', $html_str, $marks);
		$count_marks = count($marks[1]);

		for ($key_mark = 2; $key_mark < $count_marks; ++$key_mark) {

			if($link_marks == 'm' || $link_marks == $marks[1][$key_mark]){
			$html_str_cars = file_get_contents("https://www.drive2.ru".$marks[1][$key_mark]);

			$modells = array();
			preg_match_all('#<a[^>]+?class\s*?=\s*?["\']c-link c-link--text["\'][^>]+?href=["\'](.+?)["\'][^>]*?>(.+?)</a>#su', $html_str_cars, $modells);
			$count_modells = count($modells[1]);
	
			for($key_modell = 1; $key_modell < $count_modells; ++$key_modell){
				
				if($link_modells == 'm' || $link_modells == $modells[1][$key_modell]){
				curl_setopt($ch, CURLOPT_URL, "https://www.drive2.ru".$modells[1][$key_modell]); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				$html_str_cars = curl_exec($ch); 
				
				$user = array();
				$address_arr = array();
				$cars = array();
				preg_match_all('#<a[^>]+?class\s*?=\s*?["\']c-car-title  c-link["\'][^>]+?href=["\'](.+?)["\'][^>]*?>(.+?)</a>#su', $html_str_cars, $cars);
		
				preg_match_all('#<a[^>]+?class\s*?=\s*?["\']c-link c-link--color00 c-username ["\'][^>]+?href=["\'](.+?)["\'][^>]*?><span[^>]+?>(.+?)</span></a>#su', $html_str_cars, $user);
				preg_match_all('#<div[^>]+?class\s*?=\s*?["\']c-car-card__info ["\']*?><span[^>]+?title\s*?=\s*?["\']([^"]*?)["\']*?>.+?</span></div>#su', $html_str_cars, $address_arr);		

				$count_cars = count($cars[1]);
		
				for($key_car = 0; $key_car < $count_cars; ++$key_car){
					$car = array();
					$car_name = $cars[2][$key_car] ? $cars[2][$key_car] : '';
					$car_link = "https://www.drive2.ru".$cars[1][$key_car] ? "https://www.drive2.ru".$cars[1][$key_car] : '';
					$user_name = $user[2][$key_car] ? $user[2][$key_car] : ' ';
					$user_link = "https://www.drive2.ru".trim($user[1][$key_car]) ? trim("https://www.drive2.ru").$user[1][$key_car] : ' ';
		
					$address = $address_arr[1][$key_car];
		
					curl_setopt($ch, CURLOPT_URL, $car_link); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					$html_str_cars = curl_exec($ch); 
			
					preg_match_all('#<ul[^>]+?class\s*?=\s*?["\']list-compact["\'][^>]*?>(.+?)</ul>#su', $html_str_cars, $marks1);
			
					$passport = '';
					if(isset($marks1[1][0])){
						preg_match_all('#>([^<]+?)</li>#su', $marks1[1][0], $titul);
			
						foreach ($titul[1] as $value) {
							$passport .= trim($value).'; ';
						}
					}
					$query = "INSERT INTO parser (car_name, car_link, user_name, user_link, address, passport) VALUE
						('$car_name', '$car_link', '$user_name', '$user_link', '$address', '$passport')";
					mysqli_query($link, $query);
		
				}
				}
			}
			}
		}

		curl_close($ch);  
		mysqli_close($link);

			}
    function GetBase($link_marks, $link_modells) { 
		
		if($link_marks != 'm')
		$arr_marks = explode('/', $link_marks);
		if($link_modells != 'm')
		$arr_modells = explode('/', $link_modells);
		$link = mysqli_connect($this->__host, $this->__user, $this->__passw, $this->__base);

		$query = 'OPEN TABLE parser)';
		mysqli_query($link, $query);
        $query = "SELECT * FROM parser";
		$result = mysqli_query($link, $query);
		while (  $row  =  mysqli_fetch_row($result)  )
		{
			if($link_marks == 'm' && $link_modells == 'm')
				print "<br>car_name: $row[0]<br>car_link: $row[1]<br>user_name: $row[2]<br>user_link: $row[3]<br>address: $row[4]<br>passport: $row[5]<br>";
			else
			if(($link_marks != 'm' && mb_strstr($row[0], $arr_marks[1])) || ($link_modells != 'm' && mb_strstr($row[0], $arr_modells[2])))
				print "<br>car_name: $row[0]<br>car_link: $row[1]<br>user_name: $row[2]<br>user_link: $row[3]<br>address: $row[4]<br>passport: $row[5]<br>";
		};
		mysqli_close($link);
    } 
}

