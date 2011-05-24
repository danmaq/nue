<?php

require_once(NUE_LIB_ROOT . '/entity/CEntity.php');

/**
 *	状態表現のためのインターフェイス。
 */
interface IState
{

	/**
	 *	この状態が開始されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	function setup(CEntity $entity);

	/**
	 *	状態が実行されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	function execute(CEntity $entity);

	/**
	 *	別の状態へ移行される直前に呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	function teardown(CEntity $entity);
}

?>
