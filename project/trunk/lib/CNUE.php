<?php

error_reporting(E_ALL|E_STRICT);

define('NUE_ROOT', dirname(__FILE__) . '/..');
define('NUE_LIB_ROOT', dirname(__FILE__));
define('NUE_CONSTANTS', NUE_LIB_ROOT . '/CConstants.php');

require_once(NUE_LIB_ROOT . '/state/scene/initialize/CSceneParseQuery.php');

/**
 *	NUEを実行するクラス。
 */
class CNUE
{

	/**
	 *	実行します。
	 */
	public static function run()
	{
		$scene = new CEntity(CSceneParseQuery::getInstance());
		$emptyState = CEmptyState::getInstance();
		do
		{
			$scene->execute();
		}
		while($scene->getCurrentState() != $emptyState);
	}
}

?>
