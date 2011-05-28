<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/state/IState.php');

/**
 *	初回画面シーン。
 */
class CSceneHello
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
			self::$instance = new CSceneHello();
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
		$xmlbuilder = new CDocumentBuilder(_('SETUP'));
		$topic = $xmlbuilder->createTopic(_('Network Utterance Environment へようこそ'));
		$p = $xmlbuilder->createParagraph($topic);
		$xmlbuilder->addText($p,
			_('開始前にCookieが有効になっていることを確認してください。そうでない場合、途中で先に進めなくなる場合があります。'));
		$xmlbuilder->createHTMLElement($p, 'br');
		$xmlbuilder->createHTMLElement($p, 'a', array('href' => './?f=' . CConstants::STATE_USER_NEW),
			_('このリンクからスタートします。'));
		$xmlbuilder->output(CConstants::FILE_XSL_DEFAULT);
		$entity->dispose();
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
