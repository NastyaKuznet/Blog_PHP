UPDATE posts 
SET title = :title, 
    preview = :preview,
    content = :content,
    edit_date = CURRENT_TIMESTAMP,
    last_editor_id = :editor_id
WHERE id = :post_id;