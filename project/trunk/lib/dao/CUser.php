<?php

require_once('CDataEntity.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLUser.php');

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
			CDataEntity::initializeTable();
			$db = CDBManager::getInstance();
			$db->execute(CFileSQLUser::getInstance()->ddl);
			self::$users = $db->singleFetch(CFileSQLUser::getInstance()->selectCount, 'COUNT');
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
	 *
	 *	@return boolean 削除に成功した場合、true。
	 */
	public function delete()
	{
		return $db->execute(CFileSQLUser::getInstance()->delete, array('id' => $this->getID()));
	}

	/**
	 *	コミットします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	public function commit()
	{
		return CDBManager::getInstance()->execute(CFileSQLUser::getInstance()->insert,
			array('id' => $this->getID(), 'entity_id' => $this->getEntity()->id()));
	}

	/**
	 *	ロールバックします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	public function rollback()
	{
		$db = CDBManager::getInstance();
		$body = $db->execAndFetch(CFileSQLUser::getInstance()->selectFromId,
			array('id' => $this->getID()));
		$result = count($body) > 0;
		if($result)
		{
			$entity = new CDataEntity($body[0]['ID']);
			if(!$entity->rollback())
			{
				throw new Exception(_('実体は存在しません。'));
			}
			$this->entity = $entity;
			$body =& $entity->storage();
			// TODO : BODYを初期化する
		}
		return $result;
	}
}

?>
