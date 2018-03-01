<?php
	$URL = "/html/target.php/";
	
	function get_time()
	{
		$time = Array(); 
		$time = getdate();
		$time_moment = $time['mday'].".".$time['mon'].".".$time['year']." ".$time['hours'].":".$time['minutes'].":".$time['seconds']; //time +3
		return $time_moment; 
	}
	
	function get_title()
	{
		$p1 = addslashes('http://yandex.ru/');	
		$p2 = addslashes('=ru');
		$content = file_get_contents('http://yandex.ru/search/?lr=2&text=anything'); //отправляем яндексу запрос на поиск 
		$name_file = 'get_content'; 
		$num1 = strpos($content,stripslashes($p1));
		
		if($num1 !== false)
		{	
			$num2 = substr($content,$num1); 
			if(strpos($num2,$p2) !== false) 
			{
				$get_url = strip_tags(substr($num2,0,strpos($num2,stripslashes($p2))+3)); //парсим результат на предмет первого запроса в яндексе 
				$get_url = str_replace("amp;","",$get_url);					 			  //убираем теги amp из ссылки генерируемой яндексом  
				$get_page = file_get_contents($get_url);					 			  //переходим по ссылке
				preg_match("/URL=(.*)\">/" , $get_page , $url_return);		 			  //парсим присылаемый яндексом html код, в поисках конечной ссылки 
				$url_anything = $url_return[1];					      		 		      //получаем url из присылаемого html кода по запросу
				$url_anything = str_replace("'","",$url_anything);    		 			  //убираем ковычки 
				$get_page_anything = file_get_contents($url_anything);		 			  //загрузили страницу первого результата
				preg_match_all('/<title>(.*)<\/title>/', $get_page_anything, $title); 	  //парсим значение title
				//print_r($title[1]);
				$result_arr = array("date" => $title[1]);								  
				$jsone_result = json_encode($result_arr, JSON_UNESCAPED_UNICODE);		  //формируем итоговый ответ в формате json 
				print_r($jsone_result); 
			} 
		}
	}
	
	function xml_part()
	{
		if(file_exists('result.xml') == false)
		{	
			$dom = new DomDocument('1.0'); //Создает XML-строку и XML-документ при помощи DOM  
			$books = $dom->appendChild($dom->createElement('books')); //добавление корня - <books> 
			$dom->formatOutput = true; 
			$dom->saveXML(); 
			$dom->save('result.xml'); // сохранение файла 
			
		}

		$xml =simplexml_load_file('result.xml');

		
		$str = $_GET["mail"]; 

		$result = stristr($str,'@');
		if($result !== false)
		{
			$result = stristr($result,'.');
			if($result !== false)
			{
				$xml->addchild('content', $str); 
				$xml->asXML('result.xml');  
			}
			else
			{
				$xml->addchild('error', "Значение: ".$str." не подходит"); 
				$xml->asXML('result.xml'); 
			}
		}
		else
		{
			$xml->addchild('error', "Значение: ".$str." не подходит"); 
			$xml->asXML('result.xml'); 
		}		
	}	
	
	if($_SERVER['REQUEST_URI'] == $URL."anything") 
	{
		print_r(get_time()); 
	}
	
	if($_SERVER['REQUEST_URI'] == $URL."json/anything") 
	{
		get_title();
	}

	if(stristr($_SERVER['REQUEST_URI'],$URL."xml/anything") !== false) 
	{
		xml_part(); 
	}	

?>