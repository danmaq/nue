<?php

require_once('CDataEntity.php');

/**
 *	ユーザDAOクラス。
 */
abstract class CDataIndex
	implements IDAO
{

	/**	実体のメンバとデフォルト値一覧。 */
	private $format;

	/**	実体。 */
	private $entity;

	/**
	 *	コンストラクタ。
	 *
	 *	@param array $format 記憶領域のフォーマット。
	 */
	public function __construct(array $format)
	{
		$this->format = $format;
		$this->entity = new CDataEntity($format);
	}

	/**
	 *	実体オブジェクトを取得します。
	 *
	 *	@return CDataEntity 実体オブジェクト。
	 */
	public function &getEntity()
	{
		return $this->entity;
	}

	/**
	 *	実体オブジェクトを作成します。
	 *
	 *	@param string $id 実体ID(GUID)。
	 */
	protected function createEntity($id)
	{
		$entity = new CDataEntity($this->format, $id);
		if(!$entity->rollback())
		{
			throw new Exception(_('実体は存在しません。'));
		}
		$this->entity = $entity;
	}
}

?>
