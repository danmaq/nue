<?php

require_once(dirname(__FILE__) . '/../state/CEmptyState.php');

/**
 *	状態を持ったオブジェクト。
 */
class CEntity
{

	/**	前回の状態。 */
	private $previousState;

	/**	現在の状態。 */
	private $currentState;

	/**	次の状態。 */
	private $nextState;

	/**
	 *	コンストラクタ。
	 *
	 *	@param $firstState 最初の状態。既定ではnull。
	 */
	public function __construct(IState $firstState = null)
	{
		$this->previousState = CEmptyState::getInstance();
		$this->currentState = CEmptyState::getInstance();
		$this->setNextState($firstState);
		$this->commitNextState();
	}

	/**
	 *	デストラクタ。
	 */
	function __destruct()
	{
		$this->setNextState(CEmptyState::getInstance());
		$this->commitNextState();
		$this->previousState = CEmptyState::getInstance();
		$this->currentState = CEmptyState::getInstance();
		$this->nextState = null;
	}

	/**
	 *	前回の状態を取得します。
	 *
	 *	@return IState 前回の状態。
	 */
	public function getPreviousState()
	{
		return $this->previousState;
	}

	/**
	 *	現在の状態を取得します。
	 *
	 *	@return IState 現在の状態。
	 */
	public function getCurrentState()
	{
		return $this->currentState;
	}

	/**
	 *	次の状態を取得します。
	 *
	 *	@return IState 次の状態。
	 */
	public function getNextState()
	{
		return $this->nextState;
	}

	/**
	 *	次の状態を設定します。
	 *
	 *	@patam IState $nextState 次の状態。
	 */
	public function setNextState(IState $nextState = null)
	{
		$this->nextState = $nextState;
	}

	/**
	 *	実行します。
	 */
	public function execute()
	{
		$this->getCurrentState()->execute($this);
		$this->commitNextState();
	}

	/**
	 *	予約された次の状態を確定します。
	 *
	 *	@param boolean 状態が遷移した場合、true。
	 */
	public function commitNextState()
	{
		if($this->getNextState() != null)
		{
			$this->getCurrentState()->teardown($this);
		}
		$result = $this->getNextState() != null;
		if($result)
		{
			$this->previousState = $this->currentState;
			$this->currentState = $this->nextState;
			$this->nextState = null;
			$this->getCurrentState()->setup($this);
		}
		return $result;
	}
}

?>
