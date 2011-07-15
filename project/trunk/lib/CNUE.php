<?php

error_reporting(E_ALL|E_STRICT);

define('NUE_LIB_ROOT', dirname(__FILE__));
define('NUE_ROOT', realpath(NUE_LIB_ROOT . '/..'));
define('NUE_CONSTANTS', NUE_LIB_ROOT . '/CConstants.php');

require_once(NUE_LIB_ROOT . '/dao/CAccess.php');
require_once(NUE_LIB_ROOT . '/util/CErrorException.php');
require_once(NUE_LIB_ROOT . '/entity/CScene.php');
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
		date_default_timezone_set('Asia/Tokyo');
		$scene = new CScene(CSceneParseQuery::getInstance());
		$emptyState = CEmptyState::getInstance();
		do
		{
			$scene->execute();
		}
		while($scene->getCurrentState() != $emptyState);
		$access = new CAccess($scene->getUser());
		$access->commit();
		exit(0);
	}
}

?>
