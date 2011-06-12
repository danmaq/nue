<?php

require_once('CTagCategory.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTagTree.php');

/**
 *	タグのツリー キャッシュDAOクラス。
 */
class CTagTree
	extends CDataIndex
{

	/**	ネスト検出深度。 */
	const DEPTH = 5;

	/**	実体のメンバとデフォルト値一覧。 */
	private static $format = array(
		'cache' => null,
	);

	/**	初期化されたかどうか。 */
	private static $initialized = false;

	/**	タグ名。 */
	private $name = '';

	/**	カテゴリからの世代数。 */
	private $cgen = 255;

	/**
	 *	テーブルの初期化を行います。
	 */
	public static function initialize()
	{
		if(!self::$initialized)
		{
			CDataEntity::initializeTable();
			CDBManager::getInstance()->execute(CFileSQLTagTree::getInstance()->ddl);
			self::$initialized = true;
		}
	}

	/**
	 *	キャッシュをクリアます。
	 *
	 *	@return boolean 削除に成功した場合、true。
	 */
	public static function clear()
	{
		$db = CDBManager::getInstance();
		$pdo = $db->getPDO();
		$result = false;
		try
		{
			self::initialize();
			$fcache = CFileSQLTagTree::getInstance();
			$pdo->beginTransaction();
			$result = $db->execute($fcache->deleteEntity) && $db->execute($fcache->truncate);
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
		return $result;
	}

	/**
	 *	ツリー構造を取得します。
	 *
	 *	@param array $result 格納される配列。
	 *	@param string $name タグ名。
	 *	@param int $depth ネスト深度。
	 *	@return array ツリー構造の配列。
	 */
	private static function getParentTree(&$result, $name, $depth = 0)
	{
		if($depth < self::DEPTH && strlen($name) > 0)
		{
			$tag = new CTag($name);
			if($tag->rollback())
			{
				array_unshift($result, $name);
				$body &= $tag->storage();
				$category = new CTagCategory($name);
				if(!$category->isExists())
				{
					self::getParent($result, $body['parent'], $depth + 1);
				}
			}
		}
	}

	/**
	 *	カテゴリから末端までのツリー構造を取得します。
	 *
	 *	@param CTag $tag 親となるタグDAOオブジェクト。
	 *	@param int $depth ネスト深度。
	 *	@return array ツリー構造の配列。
	 */
	private static function getChildTree(CTag $tag, $depth = 0)
	{
		$result = array();
		if($depth < self::DEPTH && $tag->rollback())
		{
			foreach($tag->getChildTags() as $item)
			{
				$tags = self::getChildTree($item, $depth + 1);
				if(count($tags) > 0)
				{
					$result[$item->getID()] = $tags;
				}
				array_push($result, $item->getID());
			}
		}
		return $result;
	}

	/**
	 *	コンストラクタ。
	 *
	 *	タグ名を指定した場合、カテゴリタグまたはルートからのパンくずを格納します。
	 *	そうでない場合、カテゴリをルートと見なし、そこからのツリーを格納します。
	 *
	 *	@param string $name タグ名。省略可。
	 *	@param string $entityID 実体ID。
	 */
	public function __construct($name = '', $entityID = null)
	{
		parent::__construct(self::$format, $entityID);
		self::initialize();
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
	 *	ツリー構造を取得します。
	 *
	 *	@return array ツリー構造の配列。
	 */
	public function getTree()
	{
		$result = null;
		if($this->rollback())
		{
			$body =& $this->storage();
			$result = $body['cache'];
		}
		if($result === null || count($result) == 0)
		{
			$result = array();
			$name = $this->getID();
			if(strlen($name) === 0)
			{
				foreach(CTagCategory::getAll(false) as $item)
				{
					$tags = self::getChildTree(new CTag($item->getID()));
					if(count($tags) > 0)
					{
						$result[$item->getID()] = $tags;
					}
					array_push($result, $item->getID());
				}
			}
			else
			{
				self::getParentTree($result, $name);
				$this->cgen = count($result);
			}
			$body['cache'] = $result;
			$this->commit();
		}
		return $result;
	}

	/**
	 *	データベースに保存されているかどうかを取得します。
	 *
	 *	@return boolean 保存されている場合、true。
	 */
	public function isExists()
	{
		self::initialize();
		return CDBManager::getInstance()->singleFetch(CFileSQLTagTree::getInstance()->selectExists,
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
			self::initialize();
			$pdo->beginTransaction();
			$result = $db->execute(CFileSQLTagTree::getInstance()->delete,
				array('name' => $this->getID())) && parent::delete();
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
			$params = array(
				'name' => $this->getID(),
				'sort' => $this->cgen);
			$fcache = CFileSQLTagTree::getInstance();
			$pdo->beginTransaction();
			$result = $entity->commit() &&
				(($this->isExists() && $db->execute($fcache->update, $params)) || $db->execute(
					$fcache->insert, $params + array('entity_id' => $entity->getID())));
			if(!$result)
			{
				throw new Exception(_('DB書き込みに失敗'));
			}
			$pdo->commit();
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
		self::initialize();
		$body = CDBManager::getInstance()->execAndFetch(
			CFileSQLTagTree::getInstance()->select, array('name' => $this->getID()));
		$result = count($body) > 0;
		if($result)
		{
			$this->cgen = $body[0]['SORT'];
			$this->createEntity($body[0]['ENTITY_ID']);
		}
		return $result;
	}
}

?>
