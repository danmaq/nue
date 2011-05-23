<?php

require_once(dirname(__FILE__) . '/../../../CConstants.php');
require_once(dirname(__FILE__) . '/../../../view/CDocumentBuilder.php');
require_once(dirname(__FILE__) . '/../../IState.php');

/**
 *	モード指定が誤っている場合に呼び出されるシーンです。
 */
class CSceneSimpleError
	implements IState
{

	/**	未定義の動作状態を指定した場合のエラー クラス オブジェクト。 */
	private static $illegalMode = null;

	/**	データベース接続に失敗した場合のエラー クラス オブジェクト。 */
	private static $dbNotFound = null;

	/**	エラー メッセージ。 */
	private $description;

	/**
	 *	未定義の動作状態を指定した場合のエラー オブジェクトを取得します。
	 *
	 *	@return IState 状態のオブジェクト。
	 */
	public static function getIllegalModeInstance()
	{
		if(self::$illegalMode == null)
		{
			self::$illegalMode = new CSceneSimpleError(_('無効な動作状態。'));
		}
		return self::$illegalMode;
	}

	/**
	 *	データベース接続に失敗した場合のエラー オブジェクトを取得します。
	 *
	 *	@return IState 状態のオブジェクト。
	 */
	public static function getDBNotFoundInstance()
	{
		if(self::$dbNotFound == null)
		{
			// TODO : CSceneSimpleErrorから分離して、CDBManager.getExceptionを表示できるようにする。
			self::$dbNotFound = new CSceneSimpleError(_('データベースが見つからないか、接続できません。'));
		}
		return self::$dbNotFound;
	}

	/**
	 *	コンストラクタ。
	 *
	 *	@param string $description エラー メッセージ。
	 */
	private function __construct($description)
	{
		$this->description = $description;
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
		$xmlbuilder->createSimpleMessage(_('ERROR'), $this->description);
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
