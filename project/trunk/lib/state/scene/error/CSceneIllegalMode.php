<?php

require_once(dirname(__FILE__) . '/../../../CConstants.php');
require_once(dirname(__FILE__) . '/../../IState.php');

/**
 *	モード指定が誤っている場合に呼び出されるシーンです。
 */
class CSceneIllegalMode
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
			self::$instance = new CSceneIllegalMode();
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
		$dom = new DOMDocument('1.0', 'UTF-8');
		$body = $dom->appendChild($dom->createElement('body', _('無効な動作状態。')));
		$title = $body->appendChild($dom->createAttribute('title'));
		$title->value = 'ERROR';
		$xsl = new DOMDocument();
		$xsl->load(dirname(__FILE__) . '../../../../../skin/' . CConfigure::$SKINSET . '/default.xsl');
		$xslt = new XSLTProcessor();
		$xslt->importStyleSheet($xsl);
		echo $xslt->transformToXML($dom);
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
