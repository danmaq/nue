SELECT			TAG.NAME		AS NAME,
				TAG.ENTITY_ID	AS ENTITY_ID
	FROM		NUE_INDEX_TAG	AS TAG
	ORDER BY	(
					SELECT		COUNT(*)
						FROM	NUE_INDEX_TAG_ASSIGN
						WHERE	NAME = TAG.NAME
				) DESC,
				TAG.NAME;
