UPDATE comments
SET is_delete = TRUE,
    delete_date = CURRENT_TIMESTAMP
WHERE id = :comment_id;