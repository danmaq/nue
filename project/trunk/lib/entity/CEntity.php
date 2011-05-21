<?php

require_once(dirname(__FILE__) . '/../state/CEmptyState.php');

/**
 *	��Ԃ��������I�u�W�F�N�g�B
 */
class CEntity
{

	/**	�O��̏�ԁB */
	private $previousState;

	/**	���݂̏�ԁB */
	private $currentState;

	/**	���̏�ԁB */
	private $nextState;

	/**
	 *	�R���X�g���N�^�B
	 *
	 *	@param $firstState �ŏ��̏�ԁB����ł�null�B
	 */
	public function __construct(IState $firstState = null)
	{
		$this->previousState = CEmptyState::getInstance();
		$this->currentState = CEmptyState::getInstance();
		$this->setNextState($firstState);
		$this->commitNextState();
	}

	/**
	 *	�f�X�g���N�^�B
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
	 *	�O��̏�Ԃ��擾���܂��B
	 *
	 *	@return IState �O��̏�ԁB
	 */
	public function getPreviousState()
	{
		return $this->previousState;
	}

	/**
	 *	���݂̏�Ԃ��擾���܂��B
	 *
	 *	@return IState ���݂̏�ԁB
	 */
	public function getCurrentState()
	{
		return $this->currentState;
	}

	/**
	 *	���̏�Ԃ��擾���܂��B
	 *
	 *	@return IState ���̏�ԁB
	 */
	public function getNextState()
	{
		return $this->nextState;
	}

	/**
	 *	���̏�Ԃ�ݒ肵�܂��B
	 *
	 *	@patam IState $nextState ���̏�ԁB
	 */
	public function setNextState(IState $nextState = null)
	{
		$this->nextState = $nextState;
	}

	/**
	 *	���s���܂��B
	 */
	public function execute()
	{
		$this->getCurrentState()->execute($this);
		$this->commitNextState();
	}

	/**
	 *	�\�񂳂ꂽ���̏�Ԃ��m�肵�܂��B
	 *
	 *	@param boolean ��Ԃ��J�ڂ����ꍇ�Atrue�B
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
