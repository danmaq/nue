<?php

require_once('IDB.php');

/**
 *	MySQL��p�̃f�[�^�x�[�X�ڑ�����N���X�B
 */
class CMySQL
	implements IDB
{

	/**	�z�X�g���B */
	public $host;
	/**
	 *	�R���X�g���N�^�B
	 */
	public function __construct(string $host, integer $port, string $userId, string $password)
	{
		
	}

	/**
	 *	�ڑ����m�����܂��B
	 */
	public function connect()
	{
	}

	/**
	 *	PDO�I�u�W�F�N�g���擾���܂��B
	 *
	 *	@return mixed PDO�I�u�W�F�N�g�B
	 */
	public function getDBO()
	{
	}

	/**
	 *	�ڑ�����܂��B
	 */
	public function close()
	{
	}

	/**
	 *	�f�[�^�x�[�X����l���擾���܂��B
	 *
	 *	@param string $sql �f�[�^�x�[�X�ɓ�������N�G���B
	 *	@param integer $limit �擾���錏���B�ȗ�����(2^31)-1���B
	 *	@return mixed �l�ꗗ�B
	 */
	public function get(string $sql, integer $limit = PHP_INT_MAX)
	{
	}

}

?>
