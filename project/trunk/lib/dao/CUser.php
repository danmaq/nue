<?php

require_once('CDataIndex.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLUser.php');

/**
 *	ユーザDAOクラス。
 */
class CUser
	extends CDataIndex
	implements Serializable
{

	/**	実体のメンバとデフォルト値一覧。 */
	private static $format = array(
		'password' => '',
		'name' => '',
		'root' => false,
	);

	/**	ユーザ数。 */
	private static $users = -1;

	/**	ユーザID。 */
	private $id;

	/**
	 *	ユーザ数を取得します。
	 *
	 *	ここで同時にテーブルの初期化も行われます。
	 *
	 *	@return integer ユーザ数。
	 */
	public static function getTotalCount()
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
	 *
	 *	@param string $id ユーザID。規定値は空文字(ゲスト扱い)。
	 */
	public function __construct($id = '')
	{
		parent::__construct(self::$format);
		self::getTotalCount();
		$this->id = $id;
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
	 *	削除します。
	 *
	 *	@return boolean 削除に成功した場合、true。
	 */
	public function delete()
	{
		$id = $this->getID();
		$result = $id === '';	// IDなしはゲスト扱い
		if(!$result)
		{
			$db = CDBManager::getInstance();
			$pdo = $db->getPDO();
			try
			{
				$result = $db->execute(CFileSQLUser::getInstance()->delete,
					array('id' => $id)) && $this->getEntity()->delete();
				if($result)
				{
					self::$users--;
				}
				else
				{
					throw new Exception(_('DB書き込みに失敗'));
				}
				$pdo->commit();
			}
			catch(Exception $e)
			{
				error_log($e);
				$pdo->rollback();
			}
		}
		return $result;
	}

	/**
	 *	コミットします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	public function commit()
	{
		$id = $this->getID();
		$result = $id === '';	// IDなしはゲスト扱い
		if(!$result)
		{
			$entity = $this->getEntity();
			$db = CDBManager::getInstance();
			$pdo = $db->getPDO();
			try
			{
				$pdo->beginTransaction();
				$result = $entity->commit() && $db->execute(CFileSQLUser::getInstance()->insert,
						array('id' => $id, 'entity_id' => $entity->getID()));
				if(!$result)
				{
					throw new Exception(_('DB書き込みに失敗'));
				}
				$pdo->commit();
			}
			catch(Exception $e)
			{
				error_log($e);
				$pdo->rollback();
			}
		}
		return $result;
	}

	/**
	 *	ロールバックします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	public function rollback()
	{
		$id = $this->getID();
		$result = $id === '';	// IDなしはゲスト扱い
		if(!$result)
		{
			$db = CDBManager::getInstance();
			$body = $db->execAndFetch(CFileSQLUser::getInstance()->selectFromId,
				array('id' => $id));
			$result = count($body) > 0;
			if($result)
			{
				$entity = $this->createEntity($body[0]['ENTITY_ID']);
			}
		}
		return $result;
	}

	/**
	 *	シリアライズします。
	 *
	 *	@return string 文字列化されたデータ。
	 */
	public function serialize()
	{
		return serialize(array($this->id, $this->getEntity()->getID()));
	}

	/**
	 *	デシリアライズします。
	 *
	 *	@param string $id 実体ID(GUID)。
	 *	@return CUser ユーザ オブジェクト。
	 */
	public function unserialize($serialized)
	{
		list($id, $eid) = unserialize($serialized);
		self::__construct($id);
		$this->createEntity($eid);
	}
}

?>
