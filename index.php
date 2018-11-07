<?php
	ob_start();
	header('Content-Type: text/html;');
	error_reporting(E_ALL);
	mb_internal_encoding('utf-8');
	define('UPLOAD_DIR', dirname(__FILE__).'/'); // Константа с папкой загрузки
	/*
	Чтобы была возможность загрузить файл на сервер, нужно:

	1. добавить тег input с атрибутом type=file

	2. для тега форм добавить атрибут enctype=multipart/form-data

	3. для тега форм добавить атрибут method=post

	После отправки формы все необходимые данные по загруженным файлам будут в массиве $_FILES 
	Конечно же нужно перемесить файл из временной папки в нужную нам, используя move_uploaded_file() http://php.net/manual/ruы/function.move-uploaded-file.php 

	которая ничем не отличается от функции rename() 

	Сверстать форму с загрузкой файлов, где есть переключатель, который задает формат нового имени загружаемого файла на выбор:
	- формат 1: текущий год-месяц-день
	- формат 2: текущий год-месяц-день часы:минуты
	- формат 3: случайное имя длиной 5 символов (где цифра 5 - текстовое поле, которое можно менять)

	Файл должен загружаться в текущую папку (где расположен скрипт).
	*/
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Загрузить файл</title>
	<link rel='stylesheet' href='style.css'>
</head>
<body>
<?php

function rand_name($length){
	$rand_name = '';
    
    $a1 = array( // массив с цифрами
	    '1', '2','3', '4', '5', '6', '7', '8', '9', '0'
    );
    $a2 = array( // массив с буквами
	    'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
    );

    $arr = array_merge($a1, $a2); // складываем массивы

    for ($i = 0; $i < $length; $i++)
      $rand_name .= $arr[mt_rand(0, count($arr) - 1)]; // берём случайный элемент из массива
    return $rand_name;
}

function error_style($string){ // Стиль для текста ошибки
	return '<div class="error">'.$string.'<br></div>'; // . header("refresh: 2 ; url=index.php")
}

function success_style($string){ // Стиль для сообщения о сохранинии
	return '<div class="success">'.$string.'</div>';
}

function rus_to_lat($string) { // функция возвращает строку с замененной кириллицей
    $converter = array(
        'а' => 'a', 'б' => 'b', 'в' => 'v',
        'г' => 'g', 'д' => 'd', 'е' => 'e',
        'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
        'и' => 'i', 'й' => 'y', 'к' => 'k',
        'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r',
        'с' => 's', 'т' => 't', 'у' => 'u',
        'ф' => 'f', 'х' => 'h', 'ц' => 'c',
        'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
        'ь' => '', 'ы' => 'y', 'ъ' => '',
        'э' => 'e','ю' => 'yu', 'я' => 'ya',
        
        'А' => 'A', 'Б' => 'B', 'В' => 'V',
        'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
        'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
        'И' => 'I', 'Й' => 'Y', 'К' => 'K',
        'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R',
        'С' => 'S', 'Т' => 'T', 'У' => 'U',
        'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
        'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
        'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
    );
    return strtr($string, $converter); // заменяем кириллические символы на латиницу
}

function rus_to_url($str) { // убрать неподходящие символы
    
    $str = rus_to_lat($str); // переводим в транслит
    
    $str = strtolower($str); // в нижний регистр
    
    $space = array(' ' => '_', ':' => '-');

    $str = strtr($str, $space); // заменяем пробел на _
    
    $arr = str_split($str); // строку в массив
    $stay =  array('1', '2','3', '4', '5', '6', '7', '8', '9', '0','a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z','-','_');
    $res = array_intersect($arr, $stay); // сверяем массив строки с массивом допустимых символов
	$str = implode("", $res); // склеиваем массив в строку
    
    return $str;
}
		
?>
<h1>Загрузить файл</h1>
<?php

if(isset($_POST['upload_process'])){

	$temp = explode(".", $_FILES['filename']['name']); // имя и расширение

	$new_name = array_diff($_POST['userfilename'], array('')); // убираем пустые значения массива через сравнение

	//$new_name = implode($new_name); // Имя файла задается пользователем
	
	$new_name = reset($new_name); // выводить только первое значение массива - отмеченое имя

	$new_name = rus_to_url($new_name); // Кир на лат + убрать лишниие символы

	$file_name = $new_name . '.' . end($temp); // соединение задаваемого имя с расширением

	$errors = array();

	if(file_exists($file_name))
	die($errors[] = error_style('Файл с именем '.$file_name.' уже существует.'));

	if(filesize($_FILES['filename']['tmp_name']) > 1024 * 1024 * 10)
	die($errors[] = error_style('Файл превышает допустимый размер - 10 Мбайт'));

	if(!is_writable(UPLOAD_DIR)) 
	die($errors[] = error_style('Директория закрыта от записи.'));

	if(strlen($_FILES['filename']['tmp_name']) < 1) 
	die($errors[] = error_style('Файл для загрузки не выбран.'));

	else // Если файл загружен успешно, перемещаем его из временной директории в конечную
	{move_uploaded_file($_FILES["filename"]["tmp_name"], UPLOAD_DIR . $file_name);
	echo $success = (success_style('Файл с именем '.$file_name .' успешно загружен.<br>'));}
}

?>
	<form enctype='multipart/form-data' method='post'>
		<!--<label for="upload-photo">Browse...</label>-->
		<input type='file' name='filename' id="upload-file">
		<h2>Имя файла:</h2>
		<ul>
			<li>
				<input type='radio' name='userfilename[]' value='<?=date("Y-m-d")?>' checked='checked'>Текущая дата в формате ГГГГ-ММ-ДД
			</li>
			<li>
				<input type='radio' name='userfilename[]' value='<?=date("Y-m-d H:i")?>'>Текущая дата в формате ГГГГ-ММ-ДД_ЧЧ-ММ
			</li>
			<li>
				<input type='radio' name='userfilename[]' value='<?rand_name($length = $_POST['length'])?>'>
				Случайная строка длиной <input type='number' name='length' value='5' min='5' max='20' placeholder='5' onfocus="this.value=''"> символов
			</li>
		</ul>
		<input type='submit' id='submit' value='Сохранить' name='upload_process'>
	</form>
</body>
</html>

<?php
/*<? if(isset($_POST['length'])){$length = $_POST['length'];rand_name($length);}?>*/
//header("refresh: 2 ; url=index.php");
	exit();
?>