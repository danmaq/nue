<?php

require_once('CUser.php');
require_once('CTagAssign.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTopic.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLTagAssign.php');
require_once(NUE_LIB_ROOT . '/util/CPager.php');

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
		'description' => array(''),
	);

	/**	記事数。 */
	private static $topics = -1;

	/**	ユーザ タイムスタンプ。 */
	public $userTimeStamp;

	/**	ユーザDAOオブジェクト。 */
	private $user = null;

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

			// !!! Update patch v0.3.58->v0.3.59 !!!
			if(count($db->execAndFetch($fcache->descExistSort)) === 0)
			{
				$db->execute($fcache->alterAddSort);
			}
		}
		return self::$topics;
	}

	/**
	 *	記事数を全件取得します。
	 *
	 *	@param CPager pager ページャ オブジェクト。
	 *	@return integer 記事数。
	 */
	public static function getAll(CPager $pager = null)
	{
		$result = array();
		$totalCount = self::getTotalCount();
		if($totalCount > 0)
		{
			if($pager === null)
			{
				$pager = new CPager();
			}
			$all = CDBManager::getInstance()->execAndFetch(CFileSQLTopic::getInstance()->selectAll,
				$pager->getLimit());
			foreach($all as $item)
			{
				$topic = new CTopic($item['ID']);
				if($topic->rollback())
				{
					array_push($result, $topic);
				}
			}
			$pager->setMaxPagesFromCount($totalCount);
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
		$this->userTimeStamp = time();
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
	 *	本文を取得します。
	 *
	 *	@return array 本文。
	 */
	public function getDescription()
	{
		$body =& $this->storage();
		$result = array();
		try
		{
			$result = unserialize($body['description']);
		}
		catch(Exception $e)
		{
			// 旧バージョンからの互換用
			array_push($result, $body['description']);
		}
		return $result;
	}

	/**
	 *	製作者のユーザDAOを取得します。
	 *
	 *	@return CUser ユーザDAO。
	 */
	public function getCreatedUser()
	{
		$body =& $this->storage();
		$result = CUser::getAliasListFromEntityID($body['created_user']);
		return count($result) == 0 ? null : $result[0];
	}

	/**
	 *	タグ割り当て一覧を取得します。
	 *
	 *	@return array タグ割り当てDAO一覧
	 */
	public function getTagAssignList()
	{
		$result = array();
		$id = $this->getID();
		CTagAssign::initialize();
		foreach(CDBManager::getInstance()->execAndFetch(
			CFileSQLTagAssign::getInstance()->selectFromTopic, array('topic_id' => $id))
			as $item)
		{
			$assign = new CTagAssign($item['NAME'], $this, $item['ENTITY_ID']);
			if($assign->rollback())
			{
				array_push($result, $assign);
			}
		}
		return $result;
	}

	/**
	 *	タグを割り当てます。
	 *
	 *	@param array $words タグ名一覧。
	 */
	public function setTagAssignList(array $words)
	{
		if(count($words) > 0)
		{
			foreach(self::getTagAssignList() as $oldTag)
			{
				if(!in_array($oldTag->getTag()->getID(), $words, true))
				{
					$oldTag->delete();
				}
			}
			foreach(CTag::createTagList($words) as $newTag)
			{
				$nassign = new CTagAssign($newTag, $this);
				$nassign->commit();
			}
		}
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
				foreach($this->getTagAssignList() as $item)
				{
					$item->delete();
				}
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
			$params = array(
				'id' => $this->getID(),
				'sort' => date('Y-m-d H:i:s', $this->userTimeStamp));
			$fcache = CFileSQLTopic::getInstance();
			$pdo->beginTransaction();
			$exists = $this->isExists();
			$result = $entity->commit() &&
				$db->execute($exists ? $fcache->update : $fcache->insert, $params);
			if(!$result)
			{
				throw new Exception(_('DB書き込みに失敗'));
			}
			$pdo->commit();
			if(!$exists)
			{
				self::$topics++;
			}
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
			$body = CDBManager::getInstance()->execAndFetch(
				CFileSQLTopic::getInstance()->select, array('id' => $id));
			$result = count($body) > 0;
			if($result)
			{
				$this->userTimeStamp = $body[0]['SORT'];
				$entity = $this->createEntity($id);
			}
		}
		return $result;
	}
}

?>
