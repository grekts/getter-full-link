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
	* @param integer $errorResult Флаг, определяющий в каком формате выводить результат в случае, если ссылка не была определена (0 - в виде Exception(по-умолчанию), 1 - в виде булевого значения false)
	* @return string|object|boolean Работающая ссылка с протоколом передачи данных и, при необходимости, www. В случае ошибки ,если флаг $errorResult установлен в значение 1 - булево значение false, а если флаг не установлен, или установлен в значение 0 - объект исключения, с сообщением об ошибке.
	* @access public
	* @static
	*/
	public static function getLink($url, $errorResult = 0) {
		//Разделяем ссылку на домен и путь
		$urlPart = Url::devideUrl($url);
		//Кодируем путь, если в нем есть кирилические символы
		$encodePath = Url::encodePath($urlPart['path']);
		//Подклчюаем кодировщик домена
		$idnaConvert = new IdnaConvert();
		//Кодируем домен, если он кирилический
		$encodeDomain = $idnaConvert -> encode($urlPart['domain']);
		//Получаем готову и рабочую полную ссылку, или false
		return Url::getFullLink($encodeDomain.$encodePath, $errorResult);
	}
}