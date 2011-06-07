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
			CDBManager::getInstance()->execute(CFileSQLTagAssign::getInstance()->ddl);
			self::$initialized = true;
		}
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param mixed $mtag タグ名、またはタグDAOオブジェクト。
	 *	@param mixed $topic 記事ID、または記事DAOオブジェクト。
	 */
	public function __construct($mtag, $topic)
	{
		parent::__construct(self::$format);
		self::initialize();
		$this->name = $name;
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
			$result->rollback();
		}
		return $this->mtag;
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
		$name = $this->getID();
		$db = CDBManager::getInstance();
		$pdo = $db->getPDO();
		$result = false;
		try
		{
			self::getTotalCount();
			$pdo->beginTransaction();
			$result = $db->execute(CFileSQLTag::getInstance()->delete,
				array('name' => $name)) && parent::delete();
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
