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

	/**	ベースとなるタグDAOオブジェクト。 */
	private $base;

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
	 *	コンストラクタ。
	 *
	 *	タグ名を指定した場合、カテゴリタグまたはルートからのパンくずを格納します。
	 *	そうでない場合、カテゴリをルートと見なし、そこからのツリーを格納します。
	 *
	 *	@param string $name タグ名。省略可。
	 *	@param string $entityID 実体ID。
	 */
	public function __construct($name = null, $entityID = null)
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
		if($result === null)
		{
			$result = array();
			$name = $this->getID();
			if($name === null)
			{
				foreach(CTagCategory::getAll(false) as $item)
				{
					$tag = new CTag($item->getID());
					$result = $this->getChildTree($tag);
				}
			}
			else
			{
				$tag = new CTag($name);
				// TODO : 基準タグがある場合。
			}
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
			self::getTotalCount();
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
			$pdo->beginTransaction();
			$result = $entity->commit() && ($this->isExists() || $db->execute(
				CFileSQLTagTree::getInstance()->insert,
				array('name' => $this->getID(), 'entity_id' => $entity->getID())));
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
		$body = CDBManager::getInstance()->execAndFetch(
			CFileSQLTagTree::getInstance()->select, array('name' => $this->getID()));
		$result = count($body) > 0;
		if($result)
		{
			$this->createEntity($body[0]['ENTITY_ID']);
		}
		return $result;
	}

	/**
	 *	ツリー構造を取得します。
	 *
	 *	@param CTag $tag 親となるタグDAOオブジェクト。
	 *	@param int $depth ネスト深度。
	 *	@return array ツリー構造の配列。
	 */
	private function getChildTree(CTag $tag, $depth = 0)
	{
		$result = array();
		if($tag->rollback() && depth < self::DEPTH)
		{
			foreach($tag->getChildTags() as $item)
			{
				$tags = $this->getChildTree($item, $depth + 1);
				array_push(count($tags) === 0 ? $item->getID() : $tags);
			}
		}
		return $result;
	}
}

?>
