<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/state/IState.php');

/**
 *	ユーザを新規作成するシーンです。
 */
class CSceneNewUser
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
			self::$instance = new CSceneNewUser();
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
		$topic = $xmlbuilder->createTopic(_('管理者作成'));
		$form = $xmlbuilder->createForm($topic, './');
		$p = $xmlbuilder->createParagraph($form);
		$xmlbuilder->createTextInput($p, 'text', 'id',
			isset($_GET['id']) ? $_GET['id'] : '', _('ID(半角英数字)'), 1, 255);
		$xmlbuilder->createHTMLElement($p, 'input', array(
			'type' => 'hidden',
			'name' => 'f',
			'value' => CConstants::STATE_USER_ADD));
		$xmlbuilder->createHTMLElement($p, 'input', array(
			'type' => 'submit',
			'value' => _('登録')));
		if(isset($_GET['err']))
		{
			$p = $xmlbuilder->createParagraph($form, _('エラー'));
			$xmlbuilder->addText($p, $_GET['err']);
		}
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
