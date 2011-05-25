<?php

require_once('IDAO.php');

/*

必要な機能

総数取得
新規作成
ID指定でロード(ロールバック)
既定のロード(ロールバック)
セーブ(コミット)
権限DAO取得
メールアドレスDAO取得
作成日時取得
該当アカウント削除

*/

/**
 *	ユーザDAOクラス。
 */
class CUser
	implements IDAO
{

	/**	テーブルが初期化済みかどうか。 */
	private static $initialized = false;

	/**	ユーザ数。 */
	private static $users = -1;

	/**
	 *	ユーザ数を取得します。
	 *
	 *	@return integer ユーザ数。
	 */
	public static function getUserCount()
	{
		if(self::$users < 0)
		{
			$db = CDBManager->getInstance();
		}
		return self::$users;
	}

	/**
	 *	コンストラクタ。
	 */
	public function __construct($id = null)
	{
	}
	
	/**
	 *	実体IDを取得します。
	 *
	 *	@return string 実体ID(GUID)。
	 */
	public function getID()
	{
		return $this->id;
	}

	/**
	 *	実体オブジェクトを取得します。
	 *
	 *	@return CDataEntity 実体オブジェクト。
	 */
	public function getEntity()
	{
		return $this;
	}

	/**
	 *	削除します。
	 */
	public function delete()
	{
	}

	/**
	 *	コミットします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	public function commit()
	{
		return false;
	}

	/**
	 *	ロールバックします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	public function rollback()
	{
		return false;
	}
}

?>
