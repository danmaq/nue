UPDATE		NUE_ENTITIES
	SET		UPDATED = NOW(),
			BODY = :body
	WHERE	ID = :id;
