<?php

/**
 *	DAOクラスのインターフェイス。
 */
interface IDAO
{

	/**
	 *	IDを取得します。
	 *
	 *	@return mixed ID。
	 */
	function getID();

	/**
	 *	実体オブジェクトを取得します。
	 *
	 *	@return CDataEntity 実体オブジェクト。
	 */
	function getEntity();

	/**
	 *	データベースに保存されているかどうかを取得します。
	 *
	 *	注意: この関数は、コミットされているかどうかを保証するものではありません。
	 *
	 *	@return boolean 保存されている場合、true。
	 */
	function isExists();

	/**
	 *	削除します。
	 *
	 *	@return boolean 削除に成功した場合、true。
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
