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
		'created_user' => '',	// 実体IDを格納する
		'caption' => '',
		'description' => '',
	);

	/**	記事数。 */
	private static $topics = -1;

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
			$fcache = CFileSQLTopic::getInstance();
			$db = CDBManager::getInstance();
			$db->execute($fcache->ddl);
			self::$topics = $db->singleFetch($fcache->selectCount, 'COUNT');
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
			$all = CDBManager::getInstance()->execAndFetch(CFileSQLTopic::getInstance()->selectAll);
			foreach($all as $item)
			{
				$topic = new CTopic($item['ID']);
				if($topic->rollback())
				{
					array_push($result, $topic);
				}
			}
		}
		return $result;
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $id 記事ID。規定値はnull。
	 */
	public function __construct($id = null)
	{
		// IDなしはテンポラリ扱い
		parent::__construct(self::$format, $id);
		self::getTotalCount();
	}
	
	/**
	 *	ユーザIDを取得します。
	 *
	 *	@return string ユーザID。
	 */
	public function getID()
	{
		return $this->getEntity()->getID();
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
			CDBManager::getInstance()->singleFetch(CFileSQLTopic::getInstance()->selectExists,
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
		$result = false;	// IDなしはテンポラリ扱い
		if($id !== null)
		{
			$db = CDBManager::getInstance();
			$pdo = $db->getPDO();
			try
			{
				self::getTotalCount();
				$pdo->beginTransaction();
				$result = $db->execute(CFileSQLTopic::getInstance()->delete,
					array('id' => $id)) && parent::delete();
				if(!$result)
				{
					throw new Exception(_('DB書き込みに失敗'));
				}
				$pdo->commit();
				self::$topics--;
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
		$db = CDBManager::getInstance();
		$pdo = $db->getPDO();
		try
		{
			$pdo->beginTransaction();
			$result = $entity->commit() && ($this->isExists() || $db->execute(
				CFileSQLTopic::getInstance()->insert, array('id' => $entity->getID())));
			if(!$result)
			{
				throw new Exception(_('DB書き込みに失敗'));
			}
			$pdo->commit();
			self::$topics++;
		}
		catch(Exception $e)
		{
			error_log($e);
			$pdo->rollback();
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
			$result = $this->isExists();
			if($result)
			{
				$entity = $this->createEntity($id);
			}
		}
		return $result;
	}
}

?>
