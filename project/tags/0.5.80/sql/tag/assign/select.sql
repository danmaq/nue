SELECT		NAME,
			TOPIC_ID,
			ENTITY_ID
	FROM	NUE_INDEX_TAG_ASSIGN
	WHERE	LOWER(NAME)	= LOWER(:name) AND
			TOPIC_ID	= :topic_id;
