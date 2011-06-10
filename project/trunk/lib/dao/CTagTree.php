<?php

require_once('CTag.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTagTree.php');

/**
 *	タグのツリー キャッシュDAOクラス。
 */
class CTagTree
	extends CDataIndex
{

	/**	実体のメンバとデフォルト値一覧。 */
	private static $format = array(
		'cache' => array(),
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
		return self::createTagList($body['child']);
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
			'COUNT', array('name' => $this->getID()));
	}

	/**
	 *	タグ割り当て一覧を取得します。
	 *
	 *	注意: この処理は重いため、割り当て数を取得したい場合は
	 *	getListFromTagCount()を使用してください。
	 *
	 *	@param boolean $loadBody 実体を読み込むかどうか。既定値はtrue。
	 *	@return array 割り当てDAO一覧
	 */
	public function getListFromTag($loadBody = true)
	{
		$result = array();
		$name = $this->getID();
		CTagAssign::initialize();
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
				array('name' => $this->getID(), 'entity_id' => $entity->getID())));
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
