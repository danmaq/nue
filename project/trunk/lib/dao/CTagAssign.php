<?php

require_once('CTopic.php');
require_once('CTag.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTagAssign.php');

/**
 *	タグ割り当てDAOクラス。
 */
class CTagAssign
	extends CDataIndex
{

	/**	実体のメンバとデフォルト値一覧。 */
	private static $format = array(
		'lock' => false,
	);

	/**	初期化済みかどうか。 */
	private static $initialized = false;

	/**	タグDAOオブジェクト。 */
	private $mtag;

	/**	記事DAOオブジェクト。 */
	private $topic;

	/**
	 *	テーブルが初期化されていない場合、初期化します。
	 */
	public static function initialize()
	{
		if(!self::$initialized)
		{
			CDataEntity::initializeTable();
			CTag::getTotalCount();
			CDBManager::getInstance()->execute(CFileSQLTagAssign::getInstance()->ddl);
			self::$initialized = true;
		}
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param mixed $mtag タグ名、またはタグDAOオブジェクト。
	 *	@param mixed $topic 記事ID、または記事DAOオブジェクト。
	 *	@param string $entityID 実体ID。
	 */
	public function __construct($mtag, $topic, $entityID = null)
	{
		parent::__construct(self::$format, $entityID);
		self::initialize();
		$this->mtag = $mtag;
		$this->topic = $topic;
	}

	/**
	 *	使用できません。例外が発生します。
	 */
	public function getID()
	{
		throw new Exception(_('NOT IMPLEMENTED!'));
	}

	/**
	 *	タグDAOオブジェクトを取得します。
	 *
	 *	@return CTag タグDAOオブジェクト。
	 */
	public function getTag()
	{
		$result = $this->mtag;
		if(!($result instanceof CTag))
		{
			$result = new CTag($result);
			if($result->rollback())
			{
				$this->mtag = $result;
			}
		}
		return $result;
	}

	/**
	 *	記事DAOオブジェクトを取得します。
	 *
	 *	@return CTag 記事DAOオブジェクト。
	 */
	public function getTopic()
	{
		$result = $this->topic;
		if(!($result instanceof CTopic))
		{
			$result = new CTopic($result);
			if($result->rollback())
			{
				$this->mtag = $result;
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
		self::initialize();
		return CDBManager::getInstance()->singleFetch(
			CFileSQLTagAssign::getInstance()->selectExists, 'EXIST', $this->createDBParams());
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
			$result = $db->execute(CFileSQLTagAssign::getInstance()->delete,
				$this->createDBParams()) && parent::delete();
			if(!$result)
			{
				throw new Exception(_('DB書き込みに失敗'));
			}
			$pdo->commit();
			$tag = $this->getTag();
			// ロールバック失敗したら→ノーカウント
			if(count($tag->getListFromTag()) === 0)
			{
				$tag->delete();
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
			self::initialize();
			$pdo->beginTransaction();
			$result = $entity->commit() && ($this->isExists() || $db->execute(
				CFileSQLTagAssign::getInstance()->insert,
				$this->createDBParams() + $this->createDBParamsOnlyEID()));
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
			CFileSQLTagAssign::getInstance()->select, $this->createDBParams());
		$result = count($body) > 0;
		if($result)
		{
			$this->createEntity($body[0]['ENTITY_ID']);
		}
		return $result;
	}

	/**
	 *	データベースへ渡すID代わりとなるユニークな引数を取得します。
	 *
	 *	@return array パラメータ。
	 */
	private function createDBParams()
	{
		return array(
			'name'		=> array($this->getTag()->getID(), PDO::PARAM_STR),
			'topic_id'	=> array($this->getTopic()->getID(), PDO::PARAM_STR));
	}
}

?>
