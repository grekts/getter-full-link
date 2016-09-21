<?php

namespace getterFullLink;

use Yii;
use yii\base\ErrorException;
use getterfulllink\models\Url;
use Mso\IdnaConvert\IdnaConvert;

/**
* Класс для получения полной существующей ссылки, включающую в себя протокол передачи данных, www (при необходимости), домен и путь
*
* @author Roman Tsutskov
*/
class GetterFullLink
{
	/**
	* Метод запуска проверки ссылки
	*
	* @param string $url Ссылка, подлежаща проверке
	* @return boolean Возвращает true в случае если ссылка существует. В противном случае возвращает false
	* @access public
	* @static
	*/
	public static function getLink($url) {
		if($url !== '') {
			//Разделяем ссылку на домен и путь
			$urlPart = Url::devideUrl($url);
			//Кодируем путь, если в нем есть кирилические символы
			$encodePath = Url::encodePath($urlPart['path']);
			//Подклчюаем кодировщик домена
			$idnaConvert = new IdnaConvert();
			//Кодируем домен, если он кирилический
			$encodeDomain = $idnaConvert -> encode($urlPart['domain']);
			//Получаем готову и рабочую полную ссылку, или false
			return Url::getFullLink($encodeDomain.$encodePath);
		} else {
			throw new ErrorException("Не полученна ссылка для определения ее работоспособности");
		}
	}
}