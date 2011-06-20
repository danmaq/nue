<?php

require_once('CDataEntity.php');

/**
 *	各種インデックスDAOの基底クラス。
 */
abstract class CDataIndex
	implements IDAO
{

	/**	実体のメンバとデフォルト値一覧。 */
	private $format;

	/**	実体。 */
	protected $entity;

	/**
	 *	コンストラクタ。
	 *
	 *	@param array $format 記憶領域のフォーマット。
	 *	@param string $id 実体ID。規定値はnull。
	 */
	public function __construct(array $format, $id = null)
	{
		$this->format = $format;
		$this->entity = new CDataEntity($format, $id);
	}

	/**
	 *	実体オブジェクトを取得します。
	 *
	 *	@return CDataEntity 実体オブジェクト。
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 *	削除します。
	 *
	 *	@return boolean 削除に成功した場合、true。
	 */
	public function delete()
	{
		return $this->getEntity()->delete();
	}

	/**
	 *	記憶領域を取得します。
	 *
	 *	@return mixed 記憶領域。
	 */
	public function &storage()
	{
		return $this->getEntity()->storage();
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

	/**
	 *	DB受渡し用のパラメータを生成します。
	 *
	 *	@return array DB受渡し用のパラメータ。
	 */
	protected function createDBParamsOnlyEID()
	{
		return array('entity_id' => array($this->getEntity()->getID(), PDO::PARAM_STR));
	}
}

?>
