<?php

namespace getterfulllink\models;

use yii\base\Model;
use yii\base\ErrorException;
use yii\base\UserException;

/**
* Класс для обработки ссылки
*
* @author Roman Tsutskov
*/
class Url extends Model
{
	/**
	 * Метод разделения ссылки на домен и путь
	 * 
	 * @param string $url Часть ссылки, где указан контроллер и имя действия (получается после проверки наличия ссылки-синонима)
	 * @return array Ассоциированный массив, где по ключу 'domain' доступно имя домена из ссылки, 'path' - путь из ссылки
	 * @access public
	 * @static
	 */
	public static function devideUrl($url) {
		//Если пользователь вставил в поле неполную ссылку
		if(strpos($url, 'http') === false) {
			//Если ссылка не состоит из нескольких частей
			if(strpos($url, '/') === false) {
				return ['domain' => $url, 'path' => '/'];
			} else {
				$explodeUrl = explode('/', $url, 2);

				unset($url, $type);
				return ['domain' => $explodeUrl[0], 'path' => '/'.$explodeUrl[1]];
			}
		} else { //Если была вставлена ссылка с протоколом передачи данных
			//Парсим ссылку
			$parseUrl = parse_url($url);

			unset($url, $type);
			return ['domain' => $parseUrl['host'], 'path' => $parseUrl['path']];
		}
	}

	/**
	 * Метод кодирования пути, в котором находятся кирилические символы
	 * 
	 * @param string $puth Часть ссылки, где указан контроллер и имя действия (получается после проверки наличия ссылки-синонима)
	 * @return string Кодированная ссылка
	 * @access public
	 * @static
	 */
	public static function encodePath($puth) {
		$encodingLetters = array(array('А', '%d0%90'), array('Б', '%d0%91'), array('В', '%d0%92'), array('Г', '%d0%93'),
		array('Д', '%d0%94'), array('Е', '%d0%95'), array('Ё', '%d0%81'), array('Ж', '%d0%96'), array('З', '%d0%97'),
		array('И', '%d0%98'), array('Й', '%d0%99'), array('К', '%d0%9a'), array('Л', '%d0%9b'), array('М', '%d0%9c'),
		array('Н', '%d0%9d'), array('О', '%d0%9e'), array('П', '%d0%9f'), array('Р', '%d0%a0'), array('С', '%d0%a1'),
		array('Т', '%d0%a2'), array('У', '%d0%a3'), array('Ф', '%d0%a4'), array('Х', '%d0%a5'), array('Ц', '%d0%a6'),
		array('Ч', '%d0%a7'), array('Ш', '%d0%a8'), array('Щ', '%d0%a9'), array('Ъ', '%d0%aa'), array('Ы', '%d0%ab'),
		array('Ь', '%d0%ac'), array('Э', '%d0%ad'), array('Ю', '%d0%ae'), array('Я', '%d0%af'), array('а', '%d0%b0'),
		array('б', '%d0%b1'), array('в', '%d0%b2'), array('г', '%d0%b3'), array('д', '%d0%b4'), array('е', '%d0%b5'),
		array('ё', '%d1%91'), array('ж', '%d0%b6'), array('з', '%d0%b7'), array('и', '%d0%b8'), array('й', '%d0%b9'),
		array('к', '%d0%ba'), array('л', '%d0%bb'), array('м', '%d0%bc'), array('н', '%d0%bd'), array('о', '%d0%be'),
		array('п', '%d0%bf'), array('р', '%d1%80'), array('с', '%d1%81'), array('т', '%d1%82'), array('у', '%d1%83'),
		array('ф', '%d1%84'), array('х', '%d1%85'), array('ц', '%d1%86'), array('ч', '%d1%87'), array('ш', '%d1%88'),
		array('щ', '%d1%89'), array('ъ', '%d1%8a'), array('ы', '%d1%8b'), array('ь', '%d1%8c'), array('э', '%d1%8d'),
		array('ю', '%d1%8e'), array('я', '%d1%8f'));

		foreach($encodingLetters as $oneLineEncodingLetters) {
			$puth = preg_replace("/$oneLineEncodingLetters[0]/", "$oneLineEncodingLetters[1]", $puth);
		}

		unset($type, $encodingLetters, $oneLineEncodingLetters);
		return $puth;
	}

	/**
	 * Получение ссылки с учетом протокола передачи данных и www. Может использоваться для проверки работоспособности ссылок.
	 * 
	 * @param string $url Часть ссылки, где указан контроллер и имя действия (получается после проверки наличия ссылки-синонима)
	 * @param integer $errorResult Флаг, определяющий в каком формате выводить результат в случае, если ссылка не была определена (0 - в виде Exception(по-умолчанию), 1 - в виде булевого значения false)
	 * @return string|object|boolean Работающая ссылка с протоколом передачи данных и, при необходимости, www. В случае ошибки ,если установки флага $errorResult в значение 1 - булево значение false, а если флаг не установлен, или установлен в значение 0 - объект исключения, с сообщением об ошибке.
	 * @access public
	 * @static
	 */
	public static function getFullLink($url, $errorResult) {
		//Формируем массив с вариатами сслки
    	$urlVariants = ['http://'.$url, 'http://'.$url.'/', 'http://www.'.$url, 'http://www.'.$url.'/', 'https://'.$url, 'https://'.$url.'/', 'https://www.'.$url, 'https://www.'.$url.'/', $url];
    	//пробегаем каждый вариант ссылки
  		for ($i = 0; $i < 9; $i++) {
  			try {
				//Делаем запрос хэдера страницы
		        $curlQuery = curl_init();
		        curl_setopt($curlQuery, CURLOPT_HEADER, 1);
		        curl_setopt($curlQuery, CURLOPT_NOBODY, 1);
		        curl_setopt($curlQuery, CURLOPT_TIMEOUT, 5);
		        curl_setopt($curlQuery, CURLOPT_RETURNTRANSFER, 1);
		        curl_setopt($curlQuery, CURLOPT_SSL_VERIFYPEER, false);
		        curl_setopt($curlQuery, CURLOPT_URL, $urlVariants[$i]);
		        $headerData = curl_exec($curlQuery);
		        curl_close($curlQuery);
		    } catch (ErrorException $e) {
		    	throw new ErrorException("Ошибка системы");
		    }

	        //Если при запросе была ошибка
	        if($headerData === false) {
	        	//Если ошибка была менее восьми раз
				if($i !== 8) {
					//идем проверять следующий вариант ссылки
					continue;
				} else {
					if ($errorResult === 0) {
						throw new UserException("Указанной ссылки не существует");
					} else {
						return false;
					}
				}
	        }
	 		
	 		//Разделяем ответ сервера
	        $explodeHeaderData = explode(' ', $headerData);
	        //Если в есть код овтета 200
	        if($explodeHeaderData[1] === '200') {
	        	unset($url, $type, $curlQuery, $headerData, $explodeHeaderData);
				return $urlVariants[$i];
	        } else {
	        	//Если были проверены все варианты ссылки
				if($i === 8) {
					if ($errorResult === 0) {
						throw new UserException("Указанной ссылки не существует");
					} else {
						return false;
					}
				}
			}
	    }
	}
}