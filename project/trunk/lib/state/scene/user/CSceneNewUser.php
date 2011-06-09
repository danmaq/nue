<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CUser.php');
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
		if($entity->connectDatabase())
		{
			$xmlbuilder = new CDocumentBuilder(_('SETUP'));
			$topicName = _('管理者作成');
			if(CUser::getTotalCount() > 0)
			{
				$topicName = _('サインアップ');
				$p = $this->createForm($xmlbuilder, _('ログオン'), 'core/user/logon');
				$xmlbuilder->createTextInput(
					$p, 'password', 'pwd', '', _('パスワード(半角英数字)'), 4, 255);
			}
			$this->createForm($xmlbuilder, $topicName, 'core/user/add');
			$xmlbuilder->output(CConstants::FILE_XSL_DEFAULT);
			$entity->dispose();
		}
	}

	/**
	 *	別の状態へ移行される直前に呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function teardown(CEntity $entity)
	{
	}

	/**
	 *	フォームを作成します。
	 *
	 *	@param CDocumentBuilder $xmlbuilder DOM構築オブジェクト。
	 *	@param string $topicName トピック名。
	 *	@param string $action ジャンプ先URI。
	 */
	private function createForm(CDocumentBuilder $xmlbuilder, $topicName, $action)
	{
		$topic = $xmlbuilder->createTopic($topicName);
		$form = $xmlbuilder->createForm($topic, './');
		$result = $xmlbuilder->createParagraph($form);
		$xmlbuilder->createTextInput($result, 'text', 'id',
			isset($_GET['id']) ? $_GET['id'] : '', _('ID(半角英数字)'), 1, 255);
		$p = $xmlbuilder->createParagraph($form);
		$xmlbuilder->createHiddenInput($p, 'f', $action);
		$xmlbuilder->createSubmitInput($p, _('登録'));
		return $result;
	}
}

?>
