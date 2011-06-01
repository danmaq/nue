<?php

require_once('IDAO.php');
require_once(NUE_LIB_ROOT . '/db/CDBManager.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLEntity.php');

/**
 *	データ実体クラス。
 */
class CDataEntity
	implements IDAO
{

	/**	テーブルが初期化済みかどうか。 */
	private static $initialized = false;

	/**	実体ID(GUID)。 */
	private $id;

	/**	更新日時。 */
	private $updated;

	/**	記憶領域(連想配列)。 */
	private $body;

	/**	記憶領域のデフォルト値(連想配列)。 */
	private $format;

	/**
	 *	テーブルの有無を確認し、存在しなければ初期化します。
	 */
	public static function initializeTable()
	{
		if(!self::$initialized)
		{
			self::$initialized = true;
			CDBManager::getInstance()->execute(CFileSQLEntity::getInstance()->ddl);
		}
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param array $format 記憶領域のフォーマット。
	 *	@param string $id 実体ID(GUID)。
	 */
	public function __construct(array $format, $id = null)
	{
		if($id === null)
		{
			$id = self::createGUID();
		}
		$this->id = $id;
		$this->format = $format;
		$this->updated = time();
		$this->resetStorage();
	}

	/**
	 *	GUIDを生成します。
	 *
	 *	@return string GUID文字列。
	 */
	public static function createGUID()
	{
		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
			mt_rand(0, 65535), mt_rand(0, 65535),
			mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151),
			mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
 	}

	/**
	 *	更新日時を取得します。
	 *
	 *	@return mixed 更新日時。
	 */
	public function getUpdated()
	{
		return $this->updated;
	}

	/**
	 *	記憶領域を取得します。
	 *
	 *	@return mixed 記憶領域。
	 */
	public function &storage()
	{
		return $this->body;
	}

	/**
	 *	実体IDを取得します。
	 *
	 *	@return string 実体ID(GUID)。
	 */
	public function getID()
	{
		return $this->id;
	}

	/**
	 *	新しい実体IDを発行します。
	 */
	public function setUniqueID()
	{
		while($entity->rollback())
		{
			$entity->id = self::createGUID();
		}
	}

	/**
	 *	実体オブジェクトを取得します。
	 *
	 *	@return CDataEntity 実体オブジェクト。
	 */
	public function getEntity()
	{
		return $this;
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
		self::initializeTable();
		return CDBManager::getInstance()->singleFetch(CFileSQLEntity::getInstance()->selectExists,
			'EXIST', array('id' => $this->getID()));
	}

	/**
	 *	削除します。
	 *
	 *	@return boolean 削除に成功した場合、true。
	 */
	public function delete()
	{
		self::initializeTable();
		return $db->execute(CFileSQLEntity::getInstance()->delete, array('id' => $this->getID()));
	}

	/**
	 *	コミットします。
	 *
	 *	@param boolean $overwrite 上書きを認めるかどうか。省略時はtrue。
	 *	@return boolean 成功した場合、true。
	 */
	public function commit($overwrite = true)
	{
		self::initializeTable();
		$id = $this->getID();
		$db = CDBManager::getInstance();
		$fcache = CFileSQLEntity::getInstance();
		return $db->execute(
			$overwrite && $this->isExists() ? $fcache->update : $fcache->insert,
			array('id' => $id, 'body' => serialize($this->storage())));
	}

	/**
	 *	ロールバックします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	public function rollback()
	{
		self::initializeTable();
		$body = CDBManager::getInstance()->execAndFetch(
			CFileSQLEntity::getInstance()->select, array('id' => $this->getID()));
		$result = count($body) > 0;
		if($result)
		{
			$this->body = unserialize($body[0]['BODY']);
			$this->updated = $body[0]['UPDATED'];
			$this->resetStorage(false);
		}
		return $result;
	}

	/**
	 *	記憶領域を初期化します。
	 *
	 *	@param boolean $force 強制的にフォーマットするかどうか。
	 *		falseの場合、存在しないキーだけマージされます。
	 */
	public function resetStorage($force = true)
	{
		$format = $this->format;
		if($force)
		{
			$this->body = $format;
		}
		else
		{
			$body =& $this->body;
			$body += $format;
		}
	}
}

?>
