<?php

/**
 *	DAOクラスのインターフェイス。
 */
interface IDAO
{

	/**
	 *	IDを取得します。
	 *
	 *	@return string ID。
	 */
	function getID();

	/**
	 *	実体オブジェクトを取得します。
	 *
	 *	@return CDataEntity 実体オブジェクト。
	 */
	function &getEntity();

	/**
	 *	削除します。
	 */
	function delete();

	/**
	 *	コミットします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	function commit();

	/**
	 *	ロールバックします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	function rollback();
}

?>
