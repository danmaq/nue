<?php

require_once('CEntity.php');
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
	 *	@return boolean �ڑ��ɐ��������ꍇ�Atrue�B
	 */
	public function connectDatabase()
	{
		$db = CDBManager::getInstance();
		$result = $db->connect();
		if(!$result)
		{
			$db->close();
			$this->setNextState(CSceneDBFailed::getInstance());
		}
		return $result;
	}
}

?>
