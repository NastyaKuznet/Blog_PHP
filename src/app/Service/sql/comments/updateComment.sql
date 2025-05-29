UPDATE comments
SET content = :content,
    edit_date = CURRENT_TIMESTAMP,
    is_edit = TRUE
WHERE id = :comment_id;