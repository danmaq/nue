<?php

require_once('CDataIndex.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTag.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTagAssign.php');

/**
 *	タグDAOクラス。
 */
class CTag
	extends CDataIndex
{

	/**	実体のメンバとデフォルト値一覧。 */
	private static $format = array(
		'name' => '',
		'parent' => '',
	);

	/**	タグ数。 */
	private static $tags = -1;

	/**	タグ名。 */
	private $name;

	/**
	 *	記事数を取得します。
	 *
	 *	ここで同時にテーブルの初期化も行われます。
	 *
	 *	@return integer 記事数。
	 */
	public static function getTotalCount()
	{
		if(self::$tags < 0)
		{
			CDataEntity::initializeTable();
			$fcache = CFileSQLTag::getInstance();
			$db = CDBManager::getInstance();
			$db->execute($fcache->ddl);
			self::$topics = $db->singleFetch($fcache->selectCount, 'COUNT');
		}
		return self::$tags;
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $name タグ名。
	 */
	public function __construct($name)
	{
		parent::__construct(self::$format);
		self::getTotalCount();
		$this->name = $name;
	}
	
	/**
	 *	タグ名を取得します。
	 *
	 *	@return string タグ名。
	 */
	public function getID()
	{
		return $this->name;
	}

	/**
	 *	タグ割り当て一覧を取得します。
	 *
	 *	@return array 割り当てDAO一覧
	 */
	public function getListFromTag()
	{
		$result = array();
		$name = $this->getID();
		foreach(CDBManager::getInstance()->execAndFetch(
			CFileSQLTagAssign::getInstance()->selectFromName, array('name' => $name)) as $item)
		{
			$assign = new CTagAssign($name, $item['TOPIC_ID'], $item['ENTITY_ID']);
			if($assign->rollback())
			{
				array_push($result, $assign);
			}
		}
		return $result;
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
			CDBManager::getInstance()->singleFetch(CFileSQLTag::getInstance()->selectExists,
			'EXIST', array('name' => $this->getID()));
	}

	/**
	 *	削除します。
	 *
	 *	@return boolean 削除に成功した場合、true。
	 */
	public function delete()
	{
		$db = CDBManager::getInstance();
		$pdo = $db->getPDO();
		$result = false;
		try
		{
			self::getTotalCount();
			$pdo->beginTransaction();
			$result = $db->execute(CFileSQLTag::getInstance()->delete,
				array('name' => $this->getID())) && parent::delete();
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
				CFileSQLTag::getInstance()->insert, array('name' => $entity->getID())));
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
		$body = CDBManager::getInstance()->execAndFetch(
			CFileSQLTag::getInstance()->select, array('name' => $this->getID()));
		$result = count($body) > 0;
		if($result)
		{
			$this->createEntity($body[0]['ENTITY_ID']);
		}
		return $result;
	}
}

?>
