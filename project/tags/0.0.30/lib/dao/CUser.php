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
			$fcache = CFileSQLUser::getInstance();
			$db = CDBManager::getInstance();
			$db->execute($fcache->ddl);
			self::$users = $db->singleFetch($fcache->selectCount, 'COUNT');
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
	 *	データベースに保存されているかどうかを取得します。
	 *
	 *	注意: この関数は、コミットされているかどうかを保証するものではありません。
	 *
	 *	@return boolean 保存されている場合、true。
	 */
	public function isExists()
	{
		return self::getTotalCount() > 0 &&
			CDBManager::getInstance()->singleFetch(CFileSQLUser::getInstance()->selectExists,
			'EXIST', array('id' => $this->getID()));
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
				self::getTotalCount();
				$result = $db->execute(CFileSQLUser::getInstance()->delete,
					array('id' => $id)) && parent::delete();
				if(!$result)
				{
					throw new Exception(_('DB書き込みに失敗'));
				}
				$pdo->commit();
				self::$users--;
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
			self::getTotalCount();
			$entity = $this->getEntity();
			$db = CDBManager::getInstance();
			$pdo = $db->getPDO();
			try
			{
				$pdo->beginTransaction();
				$result = $entity->commit() && ($this->isExists() || $db->execute(
					CFileSQLUser::getInstance()->insert,
					array('id' => $id, 'entity_id' => $entity->getID())));
				if(!$result)
				{
					throw new Exception(_('DB書き込みに失敗'));
				}
				$pdo->commit();
				self::$users++;
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
			$body = $db->execAndFetch(CFileSQLUser::getInstance()->select, array('id' => $id));
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
