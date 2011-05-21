<?php

error_reporting(E_ALL|E_STRICT);

require_once(dirname(__FILE__) . '/state/scene/initialize/CSceneParseQuery.php');

/**
 *	NUE�����s����N���X�B
 */
class CNUE
{

	/**
	 *	���s���܂��B
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
