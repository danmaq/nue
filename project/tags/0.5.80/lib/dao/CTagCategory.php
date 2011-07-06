<?php

require_once('CTag.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTag.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTagCategory.php');

/**
 *	カテゴリタグDAOクラス。
 */
class CTagCategory
	extends CDataIndex
{

	/**	実体のメンバとデフォルト値一覧。 */
	private static $format = array();

	/**	タグ数。 */
	private static $tags = -1;

	/**	並び順(0～65535)。 */
	public $order;

	/**	タグ名。 */
	private $name;

	/**
	 *	タグ総数を取得します。
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
			$fcache = CFileSQLTagCategory::getInstance();
			$db = CDBManager::getInstance();
			$db->execute($fcache->ddl);
			self::$tags = $db->singleFetch($fcache->selectCount, 'COUNT');
		}
		return self::$tags;
	}

	/**
	 *	タグ一覧を取得します。
	 *
	 *	@param boolean $rollback 自動でロールバックするかどうか。規定値はtrue。
	 *	@return array タグDAOオブジェクト一覧。
	 */
	public static function getAll($rollback = true)
	{
		$result = array();
		if(self::getTotalCount() + CTag::getTotalCount() > 0)
		{
			foreach(CDBManager::getInstance()->execAndFetch(
				CFileSQLTagCategory::getInstance()->selectAll) as $item)
			{
				$tag = new CTagCategory($item['NAME'], $item['SORT'], $item['ENTITY_ID']);
				if(!$rollback || $tag->rollback())
				{
					array_push($result, $tag);
				}
			}
		}
		return $result;
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $name タグ名。
	 *	@param string $order 並び順。
	 *	@param string $entityID 実体ID。
	 */
	public function __construct($name, $order = 0, $entityID = null)
	{
		parent::__construct(self::$format, $entityID);
		self::getTotalCount();
		$this->order = $order;
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
	 *	データベースに保存されているかどうかを取得します。
	 *
	 *	注意: この関数は、コミットされているかどうかを保証するものではありません。
	 *
	 *	@return boolean 保存されている場合、true。
	 */
	public function isExists()
	{
		return self::getTotalCount() > 0 &&
			CDBManager::getInstance()->singleFetch(CFileSQLTagCategory::getInstance()->selectExists,
				'EXIST', $this->createDBParams());
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
			$result = $db->execute(CFileSQLTagCategory::getInstance()->delete,
				$this->createDBParams()) && parent::delete();
			if(!$result)
			{
				throw new Exception(_('DB書き込みに失敗'));
			}
			$pdo->commit();
			self::$tags--;
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
			$params = $this->createDBParams() + array(
				'sort' => array($this->order, PDO::PARAM_INT));
			$fcache = CFileSQLTagCategory::getInstance();
			$pdo->beginTransaction();
			$exists = $this->isExists();
			$result = $entity->commit() && (($exists && $db->execute($fcache->update, $params)) ||
				$db->execute($fcache->insert, $params + $this->createDBParamsOnlyEID()));
			if(!$result)
			{
				throw new Exception(_('DB書き込みに失敗'));
			}
			$pdo->commit();
			if(!$exists)
			{
				self::$tags++;
			}
		}
		catch(Exception $e)
		{
			error_log($e->__toString());
			error_log(print_r($pdo->errorInfo(), true));
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
			CFileSQLTagCategory::getInstance()->select, $this->createDBParams());
		$result = count($body) > 0;
		if($result)
		{
			$this->name = $body[0]['NAME'];
			$this->order = $body[0]['SORT'];
			$this->createEntity($body[0]['ENTITY_ID']);
		}
		return $result;
	}

	/**
	 *	DB受渡し用のパラメータを生成します。
	 *
	 *	@return array DB受渡し用のパラメータ。
	 */
	private function createDBParams()
	{
		return array('name' => array($this->getID(), PDO::PARAM_STR));
	}
}

?>
