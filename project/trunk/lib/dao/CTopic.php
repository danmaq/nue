<?php

require_once('CDataIndex.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTopic.php');

/**
 *	記事DAOクラス。
 */
class CTopic
	extends CDataIndex
{

	/**	実体のメンバとデフォルト値一覧。 */
	private static $format = array(
		'caption' => '',
		'description' => '',
	);

	/**	記事数。 */
	private static $topics = -1;

	/**	記事ID。 */
	private $id;

	/**
	 *	記事数を取得します。
	 *
	 *	ここで同時にテーブルの初期化も行われます。
	 *
	 *	@return integer 記事数。
	 */
	public static function getTotalCount()
	{
		if(self::$topics < 0)
		{
			CDataEntity::initializeTable();
			$db = CDBManager::getInstance();
			$db->execute(CFileSQLTopic::getInstance()->ddl);
			self::$users = $db->singleFetch(CFileSQLTopic::getInstance()->selectCount, 'COUNT');
		}
		return self::$topics;
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $id ユーザID。規定値は空文字(ゲスト扱い)。
	 */
	public function __construct($id = null)
	{
		// IDなしはテンポラリ扱い
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
		$result = false;	// IDなしはテンポラリ扱い
		if($id !== null)
		{
			$result = $db->execute(
				CFileSQLTopic::getInstance()->delete, array('id' => $id));
			if($result)
			{
				self::$topics--;
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
		$entity = $this->getEntity();
		if($entity->commit())
		{
			$result = CDBManager::getInstance()->execute(CFileSQLTopic::getInstance()->insert,
				array('id' => $entity->getID()));
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
		$result = false;
		if($id !== null)	// IDなしはテンポラリ扱い
		{
			$db = CDBManager::getInstance();
			$body = $db->execAndFetch(CFileSQLTopic::getInstance()->selectFromId,
				array('id' => $id));
			$result = count($body) > 0;
			if($result)
			{
				$entity = $this->createEntity($id);
			}
		}
		return $result;
	}
}

?>
