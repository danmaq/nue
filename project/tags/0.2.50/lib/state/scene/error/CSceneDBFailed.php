<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/db/CDBManager.php');
require_once(NUE_LIB_ROOT . '/state/IState.php');

/**
 *	データベース接続に失敗した場合に呼び出されるシーンです。
 */
class CSceneDBFailed
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CSceneDBFailed();
		}
		return self::$instance;
	}

	/**
	 *	コンストラクタ。
	 */
	private function __construct()
	{
	}

	/**
	 *	この状態が開始されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function setup(CEntity $entity)
	{
	}

	/**
	 *	状態が実行されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function execute(CEntity $entity)
	{
		$xmlbuilder = new CDocumentBuilder();
		$message = CDBManager::getInstance()->getException();
		$xmlbuilder->createSimpleMessage(
			_('ERROR'), _('データベースとの通信に失敗しました。'),
			$message->getMessage() . "\n" . $message->getTraceAsString());
		$xmlbuilder->output(CConstants::FILE_XSL_DEFAULT);
		$entity->setNextState(CEmptyState::getInstance());
	}

	/**
	 *	別の状態へ移行される直前に呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function teardown(CEntity $entity)
	{
	}
}

?>
