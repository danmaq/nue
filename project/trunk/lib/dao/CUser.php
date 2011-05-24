<?php

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
	implements IDB
{

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
	 *	データベースから値を取得します。
	 *
	 *	@param string $sql データベースに投入するクエリ。
	 *	@param integer $limit 取得する件数。省略時は(2^31)-1件。
	 *	@return mixed 値一覧。
	 */
	public function get($sql, $limit = PHP_INT_MAX)
	{
		// TODO : 未実装。
		return null;
	}
}

?>
