<?php

$A = new Drive2_auto;
if(isset($_POST['proces']) && $_POST['proces'] == 1)
	$A->GetBase();
if(isset($_POST['proces']) && $_POST['proces'] == 2)
	$A->GetInfo();

print '<form action="" method="post">
			<p><select name="proces">
			<option selected disabled>Чего вы хотите от этой программы?</option>
			<option value="1">хотите вывести информацию из базы данных?</option>
			<option value="2">хотите спарсить информацию с сайта?</option>
			</select></p>
			<p><input type="submit" value="Отправить запрос"></p>
			</form>';

class Drive2_auto { 
    private $__base = 'drive2-auto'; 
    private $__table = 'parser'; 
	
	public function Choice_Base_Table($base, $table){
		$this->__base = $base; 
		$this->__table = $table; 
	}
    
    public function GetInfo() {
		
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_URL, "example.com"); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$output = curl_exec($ch); 

		set_time_limit ( 3000 );

		$link = mysqli_connect("localhost", "root", '', $this->__base);

		$query = 'OPEN TABLE '.$this->__table.')';
		mysqli_query($link, $query);


		curl_setopt($ch, CURLOPT_URL, "https://www.drive2.ru/cars/?all"); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$html_str = curl_exec($ch);

		$marks = array();

		preg_match_all('#<a[^>]+?class\s*?=\s*?["\']c-link c-link--text["\'][^>]+?href=["\'](.+?)["\'][^>]*?>(.+?)</a>#su', $html_str, $marks);
		$count_marks = count($marks[1]);

		for ($key_mark = 2; $key_mark < $count_marks; ++$key_mark) {
	

			$html_str_cars = file_get_contents("https://www.drive2.ru".$marks[1][$key_mark]);

			$modells = array();
			preg_match_all('#<a[^>]+?class\s*?=\s*?["\']c-link c-link--text["\'][^>]+?href=["\'](.+?)["\'][^>]*?>(.+?)</a>#su', $html_str_cars, $modells);
			$count_modells = count($modells[1]);
	
			for($key_modell = 1; $key_modell < $count_modells; ++$key_modell){
		
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

		curl_close($ch);  
		mysqli_close($link);

			}
    function GetBase() { 
		$link = mysqli_connect("localhost", "root", '', "drive2-auto");

		$query = 'OPEN TABLE parser)';
		mysqli_query($link, $query);
        $query = "SELECT * FROM parser";
		$result = mysqli_query($link, $query);
		while (  $row  =  mysqli_fetch_row($result)  )
		{
		    print "<br>car_name: $row[0]<br>car_link: $row[1]<br>user_name: $row[2]<br>user_link: $row[3]<br>address: $row[4]<br>passport: $row[5]<br>";
		};
		mysqli_close($link);
    } 
}

