<?php

error_reporting(E_ALL|E_STRICT);

require_once(dirname(__FILE__) . '/state/scene/initialize/CSceneParseQuery.php');

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
