<?php

require_once('CUser.php');
require_once(NUE_LIB_ROOT . '/file/CFileSQLAccess.php');

/**
 *	アクセスDAOクラス。
 */
class CAccess
{

	/**	アクセスカウント。 */
	private static $allCount = -1;

	/**	ユーザID。 */
	private $userId = null;

	/**
	 *	アクセスカウント数を取得します。
	 *
	 *	ここで同時にテーブルの初期化も行われます。
	 *
	 *	@return integer アクセスカウント数。
	 */
	public static function getTotalCount()
	{
		if(self::$allCount < 0)
		{
			$fcache = CFileSQLAccess::getInstance();
			$db = CDBManager::getInstance();
			$db->execute($fcache->ddl);
			self::$allCount = $db->singleFetch($fcache->selectCount, 'COUNT');
		}
		return self::$allCount;
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param CUser $id 実体ID(GUID)。
	 */
	public function __construct(CUser $user = null)
	{
		self::getTotalCount();
		$this->userId = $user === null ? null : $user->getEntity()->getId();
	}

	/**
	 *	コミットします。
	 *
	 *	@return boolean 成功した場合、true。
	 */
	public function commit()
	{
		self::getTotalCount();
		$db = CDBManager::getInstance();
		$params = $_SERVER['REQUEST_METHOD'] == 'POST' ? $_POST : $_GET;
		$addr = $_SERVER['REMOTE_ADDR'];
		$result = $db->execute(CFileSQLAccess::getInstance()->insert, array(
			'user_id'		=> array($this->userId, PDO::PARAM_STR),
			'daily_id'		=> array(sha1($addr . date('d')), PDO::PARAM_STR),
			'module'		=> array($params['f'], PDO::PARAM_STR),
			'tag'			=> array(isset($params['t']) ? $params['t'] : null, PDO::PARAM_STR),
			'topic'			=> array(isset($params['id']) ? $params['id'] : null, PDO::PARAM_STR),
			'remote_host'	=> array(
				isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : $addr, PDO::PARAM_STR),
			'remote_addr'	=> array($addr, PDO::PARAM_STR),
			'user_agent'	=> array($_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR),
			'referer'		=> array($_SERVER['HTTP_REFERER'], PDO::PARAM_STR)));
		if($result)
		{
			self::$allCount++;
		}
		return $result;
	}
}

?>
