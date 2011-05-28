<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/state/IState.php');

/**
 *	シンプルなエラーメッセージを表示するシーンです。
 */
class CSceneSimpleError
	implements IState
{

	/**	未定義の動作状態を指定した場合のエラー クラス オブジェクト。 */
	private static $illegalMode = null;

	/**	セッション開始に失敗した場合のエラー クラス オブジェクト。 */
	private static $sessionFailed = null;

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
	 *	セッション開始に失敗した場合のエラー オブジェクトを取得します。
	 *
	 *	@return IState 状態のオブジェクト。
	 */
	public static function getSessionFailedInstance()
	{
		if(self::$sessionFailed == null)
		{
			self::$sessionFailed = new CSceneSimpleError(_('セッションが開始できません。Cookieがだめかもわからんね'));
		}
		return self::$sessionFailed;
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
