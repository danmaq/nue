<?php

require_once(NUE_CONSTANTS);
require_once(NUE_LIB_ROOT . '/dao/CUser.php');
require_once(NUE_LIB_ROOT . '/view/CDocumentBuilder.php');
require_once(NUE_LIB_ROOT . '/state/scene/article/CSceneBlank.php');

/**
 *	ユーザ情報を編集するシーンです。
 */
class CScenePrefUser
	implements IState
{

	/**	クラス オブジェクト。 */
	private static $instance = null;

	/**	ユーザ情報。 */
	private $user = null;

	/**
	 *	この状態のオブジェクトを取得します。
	 *
	 *	@return IState この状態のオブジェクト。
	 */
	public static function getInstance()
	{
		if(self::$instance == null)
		{
			self::$instance = new CScenePrefUser();
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
		if($entity->connectDatabase())
		{
			$entity->startSession();
			$this->user = $entity->getUser(CSceneBlank::getInstance());
		}
	}

	/**
	 *	状態が実行されたときに呼び出されます。
	 *
	 *	@param CEntity $entity この状態が適用されたオブジェクト。
	 */
	public function execute(CEntity $entity)
	{
		if($entity->getNextState() === null)
		{
			$user = $this->user;
			$body =& $user->getEntity()->storage();
			$xmlbuilder = new CDocumentBuilder(_('SETUP'));
			$xmlbuilder->createUserLogonInfo($user, false);
			$topic = $xmlbuilder->createTopic(_('ユーザ情報変更'));
			$form = $xmlbuilder->createForm($topic, './');

			$p = $xmlbuilder->createParagraph($form);
			$xmlbuilder->addText($p, _('名前とパスワードを設定してください。'));
			$xmlbuilder->createHTMLElement($p, 'br');
			$xmlbuilder->createTextInput($p, 'text', 'id',
				$user->getID(), _('ユーザID'), 1, 255, true, false);
			$xmlbuilder->addText($p, sprintf(_('管理者ですか？ : %s'),
				$body['root'] ? _('はい') : _('いいえ')));

			$p = $xmlbuilder->createParagraph($form);
			$xmlbuilder->createTextInput($p, 'text', 'name',
				isset($_GET['name']) ? $_GET['name'] : $body['name'],
				_('名前(省略時ユーザID)'), 0, 255, false);
			$reqpwd = strlen($body['password']) > 0;
			$xmlbuilder->createTextInput($p, 'password', 'pwd0',
				$reqpwd ? '' : '1', _('現在のパスワード'), 1, 255, true, $reqpwd);
			$xmlbuilder->createTextInput($p, 'password', 'pwd1',
				'', _('新しいパスワード'), 1, 255);
			$xmlbuilder->createTextInput($p, 'password', 'pwd2',
				'', _('新しいパスワード(再入力)'), 1, 255);
			$p = $xmlbuilder->createParagraph($form);
			$xmlbuilder->createHTMLElement($p, 'input', array(
				'type' => 'hidden',
				'name' => 'f',
				'value' => 'core/user/mod'));
			$xmlbuilder->createHTMLElement($p, 'input', array(
				'type' => 'submit',
				'value' => _('登録')));

			if(!$body['root'])
			{
				$topic = $xmlbuilder->createTopic(_('アカウント削除'));
				$form = $xmlbuilder->createForm($topic, './');

				$p = $xmlbuilder->createParagraph($form);
				$xmlbuilder->addText($p,
					_('やっぱりアカウント作るのやめた、またはアカウントを削除する場合、下記のボタンを押してください。'));

				$p = $xmlbuilder->createParagraph($form);
				$xmlbuilder->createHTMLElement($p, 'input', array(
					'type' => 'hidden',
					'name' => 'f',
					'value' => 'core/user/remove'));
				$xmlbuilder->createHTMLElement($p, 'input', array(
					'type' => 'submit',
					'value' => _('このアカウントを削除します。')));
			}

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
}

?>
