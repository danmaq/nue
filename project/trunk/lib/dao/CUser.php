<?php

require_once('CDataEntity.php');

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

	/**	ユーザ数。 */
	private static $users = -1;

	/**	ユーザID。 */
	private $id;

	/**	実体。 */
	private $entity;

	/**
	 *	ユーザ数を取得します。
	 *
	 *	ここで同時にテーブルの初期化も行われます。
	 *
	 *	@return integer ユーザ数。
	 */
	public static function getUserCount()
	{
		if(self::$users < 0)
		{
			$db = CDBManager->getInstance();
			$db->execute(CFileSQLEntity::getInstance()->ddl);
			self::$users = $db->singleFetch(CFileSQLEntity::getInstance()->selectCount, 'COUNT');
		}
		return self::$users;
	}

	/**
	 *	コンストラクタ。
	 */
	public function __construct($id)
	{
		self::getUserCount();
		$this->id = $id;
		$this->entity = new CDataEntity();
	}
	
	/**
	 *	ユーザIDを取得します。
	 *
	 *	@return string ユーザID。
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
		return $this->entity;
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
