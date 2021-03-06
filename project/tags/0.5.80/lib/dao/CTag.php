<?php

require_once('CTagAssign.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTag.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTagAssign.php');
require_once(NUE_LIB_ROOT . '/util/CPager.php');

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
		'childs' => array(),
	);

	/**	タグ数。 */
	private static $tags = -1;

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
			$fcache = CFileSQLTag::getInstance();
			$db = CDBManager::getInstance();
			$db->execute($fcache->ddl);
			self::$tags = $db->singleFetch($fcache->selectCount, 'COUNT');
		}
		return self::$tags;
	}

	/**
	 *	割り当てられていないタグを削除します。
	 */
	public static function cleanup()
	{
		self::getTotalCount();
		CDBManager::getInstance()->execute(CFileSQLTag::getInstance()->deleteNoAssign);
		function isRollback($v)
		{
			$tag = new CTag($v);
			return $tag->rollback();
		}
		foreach(self::getAll() as $item)
		{
			$body =& $item->storage();
			if(strlen($body['parent'] > 0) && !isRollback($body['parent']))
			{
				$body['parent'] = '';
			}
			$body['childs'] = array_filter($body['childs'], 'isRollback');
			$item->commit();
		}
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
		if(self::getTotalCount() > 0)
		{
			foreach(CDBManager::getInstance()->execAndFetch(
				CFileSQLTag::getInstance()->selectAll) as $item)
			{
				$tag = new CTag($item['NAME'], $item['ENTITY_ID']);
				if(!$rollback || $tag->rollback())
				{
					array_push($result, $tag);
				}
			}
		}
		return $result;
	}

	/**
	 *	タグ名一覧からオブジェクト一覧を作成します。
	 *
	 *	@param array $words タグ名一覧。
	 *	@return array タグDAOオブジェクト一覧。
	 */
	public static function createTagList(array $words)
	{
		$result = array();
		foreach($words as $item)
		{
			$tag = new CTag($item);
			if($tag->rollback() || $tag->commit())
			{
				array_push($result, $tag);
			}
		}
		return $result;
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $name タグ名。
	 *	@param string $entityID 実体ID。
	 */
	public function __construct($name, $entityID = null)
	{
		parent::__construct(self::$format, $entityID);
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
	 *	子タグ一覧を取得します。
	 *
	 *	@return string 子タグ一覧。
	 */
	public function getChildTags()
	{
		$body =& $this->storage();
		return self::createTagList($body['childs']);
	}

	/**
	 *	このタグの記事への割り当てられた数を取得します。
	 *
	 *	@return int 割り当て数。
	 */
	public function getListFromTagCount()
	{
		CTagAssign::initialize();
		return CDBManager::getInstance()->singleFetch(
			CFileSQLTagAssign::getInstance()->selectCountFromName,
			'COUNT', $this->createDBParams());
	}

	/**
	 *	タグ割り当て一覧を取得します。
	 *
	 *	注意: この処理は重いため、割り当て数を取得したい場合は
	 *	getListFromTagCount()を使用してください。
	 *
	 *	@param boolean $loadBody 実体を読み込むかどうか。既定値はtrue。
	 *	@param CPager $pager ページャ オブジェクト。
	 *	@return array 割り当てDAO一覧
	 */
	public function getListFromTag($loadBody = true, CPager $pager = null)
	{
		$result = array();
		if($pager === null)
		{
			$pager = new CPager();
		}

		// !!! Update patch v0.3.58->v0.3.59 !!!
		CTopic::getTotalCount();

		CTagAssign::initialize();
		foreach(CDBManager::getInstance()->execAndFetch(
			CFileSQLTagAssign::getInstance()->selectFromName,
			$this->createDBParams() + $pager->getLimit()) as $item)
		{
			$assign = new CTagAssign($item['NAME'], $item['TOPIC_ID'], $item['ENTITY_ID']);
			if($assign->rollback())
			{
				array_push($result, $assign);
			}
		}
		$pager->setMaxPagesFromCount($this->getListFromTagCount());
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
			$result = $db->execute(CFileSQLTag::getInstance()->delete,
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
			$pdo->beginTransaction();
			$exists = $this->isExists();
			$result = $entity->commit() && ($exists || $db->execute(
				CFileSQLTag::getInstance()->insert,
				$this->createDBParams() + $this->createDBParamsOnlyEID()));
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
			CFileSQLTag::getInstance()->select, $this->createDBParams());
		$result = count($body) > 0;
		if($result)
		{
			$this->name = $body[0]['NAME'];
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
