DELETE FROM	NUE_ENTITIES
	WHERE	ID IN
			(
				SELECT		ENTITY_ID
					FROM	NUE_INDEX_TAG_TREE
			);