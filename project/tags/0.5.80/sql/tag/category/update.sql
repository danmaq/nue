UPDATE		NUE_INDEX_TAG_CATEGORY
	SET		SORT = :sort
	WHERE	LOWER(NAME) = LOWER(:name);
