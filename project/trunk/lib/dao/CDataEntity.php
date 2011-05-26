<?php

require_once('IDAO.php');
require_once(NUE_ROOT . '/db/CDBManager.php');
require_once(NUE_ROOT . '/file/CFileEntity.php');

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

	/**
	 *	ユニークなGUIDを持ったインスタンスを作成します。
	 *
	 *	注意:この関数を呼び出した時点でそのGUIDは確保されます。
	 *
	 *	@return ユニークなGUIDを持ったインスタンス。
	 */
	public static function createUniqueGUIDInstance()
	{
		$entity = new CDataEntity();
		$entity->setUniqueID();
		return $entity;
	}

	/**
	 *	テーブルの有無を確認し、存在しなければ初期化します。
	 */
	private static function initializeTable()
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
	 *	@param string $id 実体ID(GUID)。
	 */
	public function __construct($id = null)
	{
		if($id === null)
		{
			$id = self::createGUID();
		}
		$this->id = $id;
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
	public function storage()
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
		$result = false;
		self::initializeTable();
		$db = CDBManager::getInstance();
		$tempEntity = new CDataEntity($this->getID());
		if($tempEntity->rollBack() && $overwrite)
		{
			$result = $db->execute(CFileSQLEntity::getInstance()->update,
				array('body' => serialize($this->storage())));
		}
		else
		{
			$result = $db->execute(CFileSQLEntity::getInstance()->insert,
				array('id' => $this->getID(), 'body' => serialize($this->storage())));
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
		self::initializeTable();
		$body = CDBManager::getInstance()->execAndFetch(
			CFileSQLEntity::getInstance()->select, array('id' => $this->getID()));
		$result = count($body) > 0;
		if($result)
		{
			$this->body = $body[0];
		}
		return $result;
	}
}

?>
