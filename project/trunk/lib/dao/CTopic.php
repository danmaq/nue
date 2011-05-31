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
		'visible' => true,
		'date' => time(),
		'created_user' => '',
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
	 *	記事数を全件取得します。
	 *
	 *	@return integer 記事数。
	 */
	public static function getAll()
	{
		$result = array();
		if(self::getTotalCount() > 0)
		{
			$all = $db->execAndFetch(CFileSQLTopic::getInstance()->selectAll);
			foreach($all as $item)
			{
				$topics = new CTopic($item['ID']);
				if($topics->rollback())
				{
					array_push($result, topics);
				}
			}
		}
		return $result;
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
			$db = CDBManager::getInstance();
			$pdo = $db->getPDO();
			try
			{
				$result = $db->execute(CFileSQLTopic::getInstance()->delete,
					array('id' => $id)) && $this->getEntity()->delete();
				if($result)
				{
					self::$topics--;
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
