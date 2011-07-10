SELECT			TAG_ASSIGN.NAME			AS NAME,
				TAG_ASSIGN.TOPIC_ID		AS TOPIC_ID,
				TAG_ASSIGN.ENTITY_ID	AS ENTITY_ID
	FROM		NUE_INDEX_TAG_ASSIGN	AS TAG_ASSIGN
	WHERE		LOWER(TAG_ASSIGN.NAME) = LOWER(:name)
	ORDER BY	(
					SELECT		TOPIC.SORT
						FROM	NUE_INDEX_TOPIC	AS TOPIC
						WHERE	TOPIC.ID = TAG_ASSIGN.TOPIC_ID
				) DESC
	LIMIT		:start, :length;