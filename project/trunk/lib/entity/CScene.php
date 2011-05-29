<?php

require_once(NUE_CONSTANTS);
require_once('CEntity.php');
require_once(NUE_LIB_ROOT . '/state/scene/error/CSceneSimpleError.php');
require_once(NUE_LIB_ROOT . '/state/scene/error/CSceneDBFailed.php');

/**
 *	��Ԃ��������I�u�W�F�N�g�B
 */
class CScene
	extends CEntity
{

	/**
	 *	�R���X�g���N�^�B
	 *
	 *	@param $firstState �ŏ��̏�ԁB����ł�null�B
	 */
	public function __construct(IState $firstState = null)
	{
		parent::__construct($firstState);
	}

	/**
	 *	�f�[�^�x�[�X�ɐڑ����܂��B
	 *
	 *	���s�����ꍇ�A�����I�ɃG���[���b�Z�[�W��\������V�[���ւƃW�����v���܂��B
	 *	�V�[���̃R�~�b�g�͍s���Ȃ����߁A�����I�ɍs�����A���݂̏�Ԃ�1���[�v���s����K�v������܂��B
	 *
	 *	@return boolean �ڑ��ɐ��������ꍇ�Atrue�B
	 */
	public function connectDatabase()
	{
		$db = CDBManager::getInstance();
		$result = $db->connect();
		if(!$result)
		{
			$this->setNextState(CSceneDBFailed::getInstance());
		}
		return $result;
	}

	/**
	 *	�Z�b�V�������J�n���܂��B
	 *
	 *	���s�����ꍇ�A�����I�ɃG���[���b�Z�[�W��\������V�[���ւƃW�����v���܂��B
	 *	�V�[���̃R�~�b�g�͍s���Ȃ����߁A�����I�ɍs�����A���݂̏�Ԃ�1���[�v���s����K�v������܂��B
	 *
	 *	@param $name �Z�b�V�������B
	 *	@return boolean �Z�b�V�����J�n�ɐ��������ꍇ�Atrue�B
	 */
	public function startSession($name = CConstants::SESSION_CORE)
	{
		try
		{
			session_name($name);
			session_start();
		}
		catch(Exception $e)
		{
			error_log($e);
			unset($_SESSION['user']);
		}
	}

	/**
	 *	���[�U�����Z�b�V�����Ɋi�[���܂��B
	 *
	 *	����: �Z�b�V�����̕ۑ��ƃN���[�Y�͎����ōs���܂���B
	 *
	 *	@param CUser $value ���[�U���Bnull��ݒ肷��Ǝ����I�ɃZ�b�V��������폜���܂��B
	 */
	public function setUser(CUser $value = null)
	{
		if($value === null)
		{
			unset($_SESSION['user']);
		}
		else
		{
			$_SESSION['user'] = $value;
		}
	}

	/**
	 *	�Z�b�V�����ɕۑ����ꂽ���[�U�����擾���܂��B
	 *
	 *	@return CUser ���[�U���B
	 */
	public function getUser()
	{
		$result = false;
		if(isset($_SESSION['user']))
		{
			$result = $_SESSION['user'];
		}
		return $result;
	}
}

?>
