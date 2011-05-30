<?php

require_once('CDataIndex.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLUser.php');

/**
 *	トピックDAOクラス。
 */
class CTopic
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
	 *
	 *	@param string $id ユーザID。規定値は空文字(ゲスト扱い)。
	 */
	public function __construct($id = '')
	{
		parent::__construct(self::$format);
		self::getUserCount();
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
			$result = $db->execute(
				CFileSQLUser::getInstance()->delete, array('id' => $this->getID()));
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
			if($entity->commit())
			{
				$result = CDBManager::getInstance()->execute(CFileSQLUser::getInstance()->insert,
					array('id' => $id, 'entity_id' => $entity->getID()));
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
				array('id' => $this->getID()));
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
		$eid = '';
		list($this->id, $eid) = unserialize($serialized);
		$this->createEntity($eid);
	}
}

?>
