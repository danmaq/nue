SELECT			NAME,
				ENTITY_ID
	FROM		NUE_INDEX_TAG_ASSIGN
	WHERE		TOPIC_ID = :topic_id
	ORDER BY	NAME;
